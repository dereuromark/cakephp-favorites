<?php
declare(strict_types=1);

namespace Favorites\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FavoritesFixture
 */
class FavoritesFixture extends TestFixture {

	/**
	 * Table name
	 *
	 * @var string
	 */
	public string $table = 'favorites_favorites';

	/**
	 * @var array
	 */
	public array $fields = [
		'id' => ['type' => 'integer', 'length' => null, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true],
		'foreign_key' => ['type' => 'integer', 'length' => null, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => ''],
		'model' => ['type' => 'string', 'length' => 80, 'null' => false, 'default' => '', 'comment' => ''],
		'user_id' => ['type' => 'integer', 'length' => null, 'unsigned' => true, 'null' => true, 'default' => null, 'comment' => ''],
		'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => ''],
		'_indexes' => [
			'user_id' => ['type' => 'index', 'columns' => ['user_id'], 'length' => []],
			'foreign_key' => ['type' => 'index', 'columns' => ['model', 'foreign_key'], 'length' => []],
		],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
		],
	];

	/**
	 * Init method
	 *
	 * @return void
	 */
	public function init(): void {
		$this->records = [
			[
				'foreign_key' => 1,
				'model' => 'Posts',
				'user_id' => 1,
				'created' => '2024-03-13 02:01:23',
			],
		];
		parent::init();
	}

}
