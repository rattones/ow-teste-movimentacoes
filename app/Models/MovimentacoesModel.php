<?php namespace App\Models;

use CodeIgniter\Model;

class MovimentacoesModel extends Model
{
  protected $table      = 'movimentacoes';
  protected $primaryKey = 'id';

  protected $returnType     = 'array';
  // protected $useSoftDeletes = true;

  protected $allowedFields = [
    'id',
    'datahora',
    'motivo',
    'valor',
    'tipo',
    'usuario_id',
    'created_at',
    'updated_at',
  ];

  // protected $useTimestamps = false;
  protected $createdField  = 'created_at';
  protected $updatedField  = 'updated_at';
  // // protected $deletedField  = 'deleted_at';

  protected $validationRules    = [
    "usuario_id" => "required",
    "datahora" => "required|valid_date",
    "valor" => "required|numeric",
    "motivo" => "required|alpha_numeric_space",
    "tipo" => "required_with[crédito,débito,estorno]",
  ];
  protected $validationMessages = [
    "usuario_id" => [
      "required" => "Campo obrigatório: ID de usuário",
    ],
    "datahora" => [
      "required" => "Campo obrigatório: Data/Hora da transação",
      "valid_date" => "Data/Hora inválida",
    ],
    "valor" => [
      "required" => "Campo obrigatório: Valor",
      "numeric" => "Valor deve ser numérico",
    ],
    "motivo" => [
      "required" => "Campo obrigatório: Motivo",
      "alpha_numeric_space" => "Caractere inválido",
    ],
    "tipo" => [
      "required_with" => "Tipo inválido",
    ],
  ];
  // protected $skipValidation     = false;
}
