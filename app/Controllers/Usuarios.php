<?php
namespace App\Controllers;

use App\Models\UsuariosModel;
use CodeIgniter\API\ResponseTrait;
use DateInterval;
use DateTime;

class Usuarios extends BaseController
{
  use ResponseTrait;

  /**
   * inserir um novo usuários
   */
  public function create()
  {
    $data= $this->request->getJSON();

    $model = new UsuariosModel();

    $data = [
      "id" => \App\Libraries\UUID::v4(),
      "nome" => @$data->nome,
      "email" => @$data->email,
      "data_nascimento" => @$data->data_nascimento,
      "saldo" => (isset($data->saldo))? $data->saldo: 0,
      "created_at" => date('Y-m-d H:i:s'),
      "updated_at" => "null",
    ];

    if (!isset($data['data_nascimento'])) {
      return $this->fail(['message'=>'Campo obrigatório: Data de Nascimento'], 204);
    }
    // validando idade
    $hoje= new DateTime(date('Y-m-d'));
    $niver= new DateTime($data['data_nascimento']);
    $idade= $niver->diff($hoje);

    if ($idade->y < 18) {
      return $this->fail(['message'=>'Usuário deve ter 18 anos ou mais para criar conta']);
    }
    echo '<pre>'; var_dump($idade->y); die;

    if (!(!!$model->insert($data, false))) {
      return $this->fail($model->validation->getErrors());
    }

    return $this->respond($data);
  }

  /**
   * retorna a lista ordenada por data de criação
   */
  public function list()
  {
    $model= new UsuariosModel();

    $list= $model->orderBy('created_at')->findAll();

    if (empty($list)) {
      return $this->respondNoContent('Nenhum usuário encontrado');
    }

    return $this->respond($list);
  }

  /**
   * retorna um único usuário
   */
  public function get()
  {
    $params = $this->request->uri->getSegment(2);
    $model= new UsuariosModel();

    $list= $model->find($params);

    if (is_null($list)) {
      return $this->respondNoContent('Usuário não encontrado');
    }

    return $this->respond($list);
  }

  /**
   * exclui um dado usuário
   */
  public function delete()
  {
    $params = $this->request->uri->getSegment(2);
    $model= new UsuariosModel();

    // verificando se o usuário tem saldo e/ou movimentação
    $movi= new \App\Controllers\Movimentacoes();
    $list= $movi->getList($params, true);

    if (empty($list['usuario'])) {
      return $this->respondNoContent('Usuário não encontrado');
    }

    if ($list['usuario']['saldo'] != 0 or !empty($list['movimentacoes'])) {
      return $this->fail('Usuário já possui saldo e/ou movimentação cadastrado');
    }

    $model->delete($params, true);

    if ( is_null($model->find($params)) ) {
      return $this->respond(['message'=>'Usuário removido com sucesso']);
    }

    return $this->respondDeleted($params);
  }

  /**
   * altera o saldo de um usuário
   */
  public function addSaldo()
  {
    $data= $this->request->getJSON();
    $token= $this->getToken();

    $model = new UsuariosModel();

    $data = [
      "saldo" => $data->saldo,
      "updated_at" => date('Y-m-d H:i:s'),
    ];

    if (!(!!$model->update($token, $data, false))) {
      return $this->fail($model->validation->getErrors());
    }

    return $this->respond($data);
  }
}
