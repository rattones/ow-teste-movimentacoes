<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlteraUsuarios extends Migration
{
	public function up()
	{
    // criando campo
    $this->forge->addColumn('usuarios', [
      'saldo' => [
        'type' => 'float',
        'default' => 0,
      ],
    ]);
	}

	//--------------------------------------------------------------------

	public function down()
	{
    // removendo campo
    $this->forge->dropColumn('usuarios', 'saldo');
	}
}
