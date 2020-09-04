<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TabelaMovimantacoes extends Migration
{
	public function up()
	{
		// criando tabela de movimentações
		// adicionando campos
		$this->forge->addField([
			// id - PK
			'id' => [
				'type' => 'varchar',
				'constraint' => 50,
			],
			// data hora da movimentação
			'datahora' => [
				'type' => 'datetime',
			],
			// motivo - texto livre para o usuário
			'motivo' => [
				'type' => 'varchar', 
				'constraint' => 200, 
			],
			// valor movimentado
			'valor' => [
				'type' => 'float',
			],
			// tipo de movimentação realizado 
			'tipo' => [
				'type' => 'enum',
				'constraint' => [
					'débito', 'crédito', 'estorno',
				],
			],
			// usuário da movimentação
			'usuario_id' => [
				'type' => 'varchar',
				'constraint' => 50,
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
		// chave estrangeira
		$this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'RESTRICT');
		// criando a tabela
		$this->forge->createTable('movimentacoes');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		// excluindo chave estrangeira
		$this->forge->dropForeignKey('movimentacoes', 'movimentacoes_usuario_id_foreign');
		// excluindo tabela criada
		$this->forge->dropTable('movimentacoes');
	}
}
