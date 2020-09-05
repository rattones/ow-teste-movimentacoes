<?php namespace App\Models;

use CodeIgniter\Model;

class UsuariosModel extends Model
{
  protected $table      = 'usuarios';
  protected $primaryKey = 'id';

  protected $returnType     = 'array';
  // protected $useSoftDeletes = true;

  protected $allowedFields = [
    'id',
    'nome', 
    'email', 
    'data_nascimento',
    'created_at',
    'updated_at',
  ];

  // protected $useTimestamps = false;
  protected $createdField  = 'created_at';
  protected $updatedField  = 'updated_at';
  // // protected $deletedField  = 'deleted_at';

  protected $validationRules    = [
    "nome" => "required|alpha_numeric_space",
    "email" => "required|valid_email|is_unique[usuarios.email]",
    "data_nascimento" => "required|valid_date"
  ];
  protected $validationMessages = [
    "nome" => [
      "required" => "Campo obrigatório: Nome",
    ],
    "email" => [
      "required" => "Campo obrigatório: Email",
      "is_unique" => "Email já cadastrado",
    ],
    "data_nascimento" => [
      "required" => "Campo obrigatório: Data de Nascimento",      
    ],
  ];
  // protected $skipValidation     = false;
}