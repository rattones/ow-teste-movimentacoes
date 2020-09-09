<?php
namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * criando controller básico para otimizar
 */
class BaseController extends Controller
{

  /**
   * retorna o token : usuario_id da movimentacao
   */
  protected function getToken(): string
  {
    // buscando e tratando token
    $token= $this->request->getHeaders();
    if (!isset($token['Authorization'])) {
      return "";
    }
    $aux= explode(' ', $token['Authorization']->getValue());
    return $aux[1];
  }

}
