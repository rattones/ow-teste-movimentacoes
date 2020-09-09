<?php
namespace App\Controllers;

use App\Models\MovimentacoesModel;
use CodeIgniter\API\ResponseTrait;
use DateInterval;
use DateTime;

class Movimentacoes extends BaseController
{
  use ResponseTrait;

  /**
   * inserir um nova movimentação
   */
  public function create()
  {
    $data= $this->request->getJSON();

    $model = new MovimentacoesModel();

    $data = [
      "id" => \App\Libraries\UUID::v4(),
      "datahora" => $data->datahora,
      "motivo" => $data->motivo,
      "valor" => $data->valor,
      "tipo" => $data->tipo,
      "usuario_id" => $this->getToken(),
      "created_at" => date('Y-m-d H:i:s'),
      "updated_at" => "null",
    ];

    if (!(!!$model->insert($data, false))) {
      return $this->fail($model->validation->getErrors());
    }

    return $this->respond($data);
  }

  /**
   * busca os dados de movimentação do usuário
   */
  public function getList(string $token= null, $all= false)
  {
    if (is_null($token)) {
      $token= $this->getToken();
      $pag = (!!$this->request->uri->getSegment(2))? $this->request->uri->getSegment(2): 0;
      $offset= 10;
      $limit= $pag*$offset;
    }

    $model= new MovimentacoesModel();
    $usuario= new \App\Models\UsuariosModel();

    $list= [];

    $list['usuario']= $usuario->find($token);

    if ($all) {
      $list['movimentacoes']= $model->where('usuario_id', $token)
                  ->orderBy('datahora, created_at')
                  ->findAll();
    } else {
      $list['movimentacoes']= $model->where('usuario_id', $token)
                  ->orderBy('datahora, created_at')
                  ->findAll($limit, $offset);
    }

    if (empty($list)) {
      return [];
    }

    return $list;

  }

  /**
   * retorna a lista de movimentações do usuário
   */
  public function list()
  {
    $list= $this->getList();

    if (empty($list)) {
      return $this->respondNoContent('Nenhuma movimentação encontrada');
    }

    return $this->respond($list);
  }

  /**
   * retorna uma movimentação e os dados do usuário
   */
  public function get()
  {
    $params = $this->request->uri->getSegment(2);
    $model= new MovimentacoesModel();

    $list= $model->find($params);

    if (is_null($list)) {
      return $this->respondNoContent('Movimentação não encontrada');
    }

    return $this->respond($list);
  }

  /**
   * excluir uma movimentação do usuário
   */
  public function delete()
  {
    $params = $this->request->uri->getSegment(2);
    $model= new MovimentacoesModel();

    $model->delete($params, true);

    if ( !is_null($model->find($params)) ) {
      return $this->respondNoContent('Movimentação não encontrada');
    }

    return $this->respondDeleted($params);
  }

  /**
   * valida uma string como sendo um período válido
   */
  private function validatePeriod(string $value): bool
  {
    // validando tamanho
    if (strlen($value) != 7 or !strpos($value, '-')) {
      return false;
    }
    // validando ano
    list($ano, $mes)= explode('-', $value);
    if (!($ano >= 1900 and $ano <= 2199)) {
      return false;
    }
    // validando data
    if (!($mes >= 1 and $mes <= 12)) {
      return false;
    }
    return true;
  }

  /**
   * relatório de movimentações
   */
  public function report()
  {
    $model= new MovimentacoesModel();
    $data= $this->request->getJSON();
    $token= $this->getToken();

    if (empty($token)) {
      return $this->fail('Token de autenticação do usuário necessário', 403);
    }

    $userModel= new \App\Models\UsuariosModel();
    $usuario= $userModel->find($token);

    if (is_null($data)) {
      return $this->respond(['message'=>'Parâmetro obrigatório']);
    }

    $condicao= '';
    // verificando solicitação
    if ($data->tipo == '30') {
      $inicio= new DateTime(date('Y-m-d'));
      $fim= new DateTime(date('Y-m-d'));
      $inicio= $inicio->sub(new DateInterval('P30D'));
      $inicio= $inicio->format('Y-m-d');
      $fim= $fim->format('Y-m-d');
      $condicao= "datahora between '{$inicio}' and '{$fim}'";
    } elseif ($this->validatePeriod($data->tipo)) {
      $inicio= new DateTime(date("{$data->tipo}-01"));
      $fim= new DateTime(date("{$data->tipo}-01"));
      $fim= $fim->add(new DateInterval('P1M'))->sub(new DateInterval('P1D'));
      $inicio= $inicio->format('Y-m-d');
      $fim= $fim->format('Y-m-d');
      $condicao= "`datahora` between '{$inicio}' and '{$fim}'";
    } elseif (!empty($data->tipo)) {
      return $this->respond(['message'=>'Tipo inválido'], 400);
    }

    if (empty($condicao)) {
      $list= $model->where('usuario_id', $token)
                ->orderBy('datahora, created_at')
                ->findAll();
    } else {
      $list= $model->where('usuario_id', $token)
                ->where($condicao)
                ->orderBy('datahora, created_at')
                ->findAll();
    }

    // gerar dados para arquivo .csv
    $header= "{$usuario['nome']};{$usuario['email']};{$usuario['data_nascimento']}"."\n";
    $content= "";
    foreach ($list as $item) {
      $content.= "{$item['datahora']};{$item['motivo']};{$item['valor']};{$item['tipo']}"."\n";
    }
    $saldo= $this->getSaldo($token);
    $hoje= date('Y-m-d H:i:s');
    $footer= "{$hoje};saldo total;{$saldo}";

    $fileContent= $header.$content.$footer;
    $filename= date('YmsHis')."{$usuario['id']}.csv";

    return $this->response->download($filename, $fileContent);
  }

  /**
   * retorna o valor do saldo
   */
  private function getSaldo(string $token= null): ?float
  {
    $list= $this->getList($token, true);

    if (empty($list['movimentacoes'])) {
      return null;
    }

    $total= $list['usuario']['saldo'];
    foreach ($list['movimentacoes'] as $mov) {
      $total+= ($mov['tipo'] == 'débito')? -1*$mov['valor']: $mov['valor'];
    }

    return $total;
  }

  /**
   * retorna o saldo do usuário
   */
  public function saldo(string $token= null)
  {
    $total= $this->getSaldo($token);

    if (isnull($total)) {
      return $this->respondNoContent('Nenhuma movimentação encontrada');
    }

    return $this->respond(['saldo'=>$total]);
  }
}
