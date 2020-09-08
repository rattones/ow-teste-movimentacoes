<?php
namespace App\Controllers;

use App\Models\MovimentacoesModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use DateInterval;
use DateTime;

class Movimentacoes extends Controller
{
  use ResponseTrait;

  /**
   * retorna o token : usuario_id da movimentacao
   */
  private function getToken(): string
  {
    // buscando e tratando token
    $token= $this->request->getHeaders();
    $aux= explode(' ', $token['Authorization']->getValue());
    return $aux[1];
  }

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

  public function list()
  {
    $token= $this->getToken();
    $pag = (!!$this->request->uri->getSegment(2))? $this->request->uri->getSegment(2): 0;
    $offset= 10;
    $limit= $pag*$offset;

    $model= new MovimentacoesModel();
    $usuario= new \App\Models\UsuariosModel();

    $list= [];

    $list['usuario']= $usuario->find($token);

    $list['movimentacoes']= $model->where('usuario_id', $token)
                ->orderBy('datahora, created_at')
                ->findAll($limit, $offset);

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

    // echo '<pre>'; print_r([$condicao]); die;

    if (empty($condicao)) {
      $list= $model->orderBy('datahora, created_at')
                ->findAll();
    } else {
      $list= $model->where($condicao)
                ->orderBy('datahora, created_at')
                ->findAll();
    }

    return $this->respond($list);
  }
}
