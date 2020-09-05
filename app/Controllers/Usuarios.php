<?php
namespace App\Controllers;

use App\Models\UsuariosModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

class Usuarios extends Controller
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
      "nome" => $data->nome,
      "email" => $data->email,
      "data_nascimento" => $data->data_nascimento,
      "created_at" => date('Y-m-d H:i:s'),
      "updated_at" => "null",
    ];

    if (!!$model->insert($data)) {
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

    $model->delete($params, true);

    if ( is_null($model->find($params)) ) { 
      return $this->respondNoContent('Usuário não encontrado');
    }

    return $this->respondDeleted($params);
  }
}