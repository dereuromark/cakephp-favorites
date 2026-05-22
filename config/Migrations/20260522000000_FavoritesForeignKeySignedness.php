<?php
declare(strict_types=1);

use Cake\Core\Configure;
use Migrations\BaseMigration;

class FavoritesForeignKeySignedness extends BaseMigration {

	/**
	 * The `foreign_key` (polymorphic host record) and `user_id` columns
	 * reference primary keys, so they must use the same signedness as the
	 * application's primary keys, governed by the
	 * `Migrations.unsigned_primary_keys` flag. The original migration hardcoded
	 * them as unsigned, which mismatches signed-primary-key apps.
	 * Signedness only takes effect on MySQL; SQLite/Postgres ignore it.
	 *
	 * @return void
	 */
	public function up(): void {
		// Match the application's primary-key signedness. The flag is false
		// (signed primary keys) when unset, so an unset flag yields signed columns,
		// matching the default-signed ids they reference. Pass the default
		// explicitly to make that intent unmistakable. (Unsigned only on MySQL.)
		$signed = !(bool)Configure::read('Migrations.unsigned_primary_keys', false);

		$this->table('favorites_favorites')
			->changeColumn('foreign_key', 'integer', [
				'default' => null,
				'null' => false,
				'signed' => $signed,
			])
			->changeColumn('user_id', 'integer', [
				'default' => null,
				'null' => false,
				'signed' => $signed,
			])
			->update();
	}

	/**
	 * @return void
	 */
	public function down(): void {
		$this->table('favorites_favorites')
			->changeColumn('foreign_key', 'integer', [
				'default' => null,
				'null' => false,
				'signed' => false,
			])
			->changeColumn('user_id', 'integer', [
				'default' => null,
				'null' => false,
				'signed' => false,
			])
			->update();
	}

}
