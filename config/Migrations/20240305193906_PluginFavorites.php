<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class PluginFavorites extends AbstractMigration {

	/**
	 * Change Method.
	 *
	 * More information on this method is available here:
	 * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
	 *
	 * @return void
	 */
	public function change(): void {
		$this->table('favorites_favorites')
			->addColumn('foreign_key', 'integer', [
				'default' => null,
				'null' => false,
				'signed' => false,
			])
			->addColumn('model', 'string', [
				'default' => '',
				'limit' => 80,
				'null' => false,
			])
			->addColumn('user_id', 'integer', [
				'default' => null,
				'null' => true,
				'signed' => false,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'null' => false,
			])
			->addIndex(
				[
					'user_id',
				],
				[
					'name' => 'user_id',
				],
			)
			->addIndex(
				[
					'model',
					'foreign_key',
				],
				[
					'name' => 'foreign_key',
				],
			)
			->addIndex(
				[
					'model',
					'foreign_key',
					'user_id',
				],
				[
					'name' => 'per_user',
					'unique' => true,
				],
			)
			->create();
	}

}
