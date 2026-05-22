<?php
declare(strict_types=1);

use Cake\Core\Configure;
use Migrations\BaseMigration;

class PluginFavorites extends BaseMigration {

	/**
	 * Change Method.
	 *
	 * More information on this method is available here:
	 * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
	 *
	 * @return void
	 */
	public function change(): void {
		// foreign_key (polymorphic host record) follows the Polymorphic.type config so
		// apps using UUID primary keys can store matching foreign keys. user_id is a
		// concrete FK to app users and always follows the primary-key signedness.
		$type = (string)Configure::read('Polymorphic.type', 'integer');
		$signed = !(bool)Configure::read('Migrations.unsigned_primary_keys', false);

		$polymorphicOptions = [
			'default' => null,
			'null' => false,
		];
		if (in_array($type, ['integer', 'biginteger'], true)) {
			$polymorphicOptions['signed'] = $signed;
		}

		$this->table('favorites_favorites')
			->addColumn('foreign_key', $type, $polymorphicOptions)
			->addColumn('model', 'string', [
				'default' => null,
				'limit' => 80,
				'null' => false,
			])
			->addColumn('user_id', 'integer', [
				'default' => null,
				'null' => false,
				'signed' => $signed,
			])
			->addColumn('value', 'tinyinteger', [
				'default' => null,
				'null' => true,
				'signed' => true,
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
					'name' => 'favorite_user_id',
				],
			)
			->addIndex(
				[
					'model',
					'foreign_key',
				],
				[
					'name' => 'favorite_foreign_key',
				],
			)
			->addIndex(
				[
					'model',
					'foreign_key',
					'user_id',
					'value',
				],
				[
					'name' => 'favorite_unique',
					'unique' => true,
				],
			)
			->create();
	}

}
