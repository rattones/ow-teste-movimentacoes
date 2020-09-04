<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TabelaUsuarios extends Migration
{
	public function up()
	{
		// criando tabela de usuários
		// adicionando os campos
		$this->forge->addField([
			// id - PK
			'id' => [
				'type' => 'varchar',
				'constraint' => 50,
			],
			// nome do usuário
			'name' => [
				'type' => 'varchar',
				'constraint' => 200,
			],
			// email de contato - Unique
			'email' => [
				'type' => 'varchar',
				'constraint' => 200,
			],
			// data de aniversário
			'brithday' => [
				'type' => 'date',
			],
			// campo de controle de criação do registro
			'created_at' => [
				'type' => 'datetime',
			],
			// campo de controle da última alteração do resgistro
			'updated_at' => [
				'type' => 'datetime',
			],
		]);
		// adicionando chave primaria
		$this->forge->addPrimaryKey('id');
		// adicionando campo único
		$this->forge->addUniqueKey('email');
		// criando a tabela
		$this->forge->createTable('usuarios');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		// excluindo tabela criada
		$this->forge->dropTable('usuarios');
	}
}
