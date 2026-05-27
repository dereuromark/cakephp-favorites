<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Model\Table;

use Cake\Database\Exception\DatabaseException;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use Favorites\Model\Table\FavoritesTable;
use PDOException;
use ReflectionClass;

/**
 * Favorites\Model\Table\FavoritesTable Test Case
 */
class FavoritesTableTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \Favorites\Model\Table\FavoritesTable
	 */
	protected $Favorites;

	/**
	 * Fixtures
	 *
	 * @var list<string>
	 */
	protected array $fixtures = [
		'plugin.Favorites.Favorites',
		'plugin.Favorites.Users',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		$config = $this->getTableLocator()->exists('Favorites') ? [] : ['className' => FavoritesTable::class];
		$this->Favorites = $this->getTableLocator()->get('Favorites', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		unset($this->Favorites);

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testAdd(): void {
		$favorites = $this->Favorites->find()->all()->toArray();
		$this->assertCount(1, $favorites);

		$result = $this->Favorites->add('Posts', 1, 1);
		$this->assertFalse($result->isNew());

		$favorites = $this->Favorites->find()->all()->toArray();
		$this->assertCount(1, $favorites);

		$result = $this->Favorites->add('X', 1, 1);
		$this->assertFalse($result->isNew());

		$favorites = $this->Favorites->find()->all()->toArray();
		$this->assertCount(2, $favorites);
	}

	/**
	 * @return void
	 */
	public function testAddValue(): void {
		$favorites = $this->Favorites->find()->all()->toArray();
		$this->assertCount(1, $favorites);

		$result = $this->Favorites->add('Posts', 1, 1, 1);
		$this->assertSame(1, $result->value);

		$result = $this->Favorites->add('Posts', 1, 1, -1);
		$this->assertSame(-1, $result->value);

		$favorites = $this->Favorites->find()->all()->toArray();
		$this->assertCount(1, $favorites);
	}

	/**
	 * @return void
	 */
	public function testAddWithUuidForeignKey(): void {
		$uuid = '550e8400-e29b-41d4-a716-446655440000';

		$this->withStringForeignKeyColumn(function () use ($uuid): void {
			$result = $this->Favorites->add('UuidPosts', $uuid, 1);

			$this->assertFalse($result->isNew());
			$this->assertSame($uuid, $result->foreign_key);
			$this->assertSame($uuid, $this->Favorites->find()->where(['model' => 'UuidPosts'])->firstOrFail()->foreign_key);
		});
	}

	/**
	 * Regression for the tightened unique index. The application path
	 * (`FavoritesTable::add()`) already uses `findOrCreate` so it
	 * doesn't accidentally duplicate, but the DB-level constraint
	 * previously allowed duplicate `(model, foreign_key, user_id)`
	 * rows as long as `value` differed. Any direct INSERT — a custom
	 * import, a race between two `findOrCreate` calls, or a future
	 * `newEntity()` + `save()` path — would land both rows.
	 *
	 * @return void
	 */
	public function testUniqueIndexPreventsDuplicatePerUserPerRecord(): void {
		// Wipe the fixture row so the asserts measure the constraint, not it.
		$this->Favorites->deleteAll(['1=1']);

		// Build directly via set(guard=false) — the entity's `_accessible`
		// allowlist blocks mass-assign of identity columns by design
		// (those are set server-side via FavoritesTable::add).
		$first = $this->Favorites->newEmptyEntity();
		$first->patch(['model' => 'Posts', 'foreign_key' => 1, 'user_id' => 1, 'value' => 1], ['guard' => false]);
		$this->Favorites->saveOrFail($first);

		$second = $this->Favorites->newEmptyEntity();
		$second->patch(['model' => 'Posts', 'foreign_key' => 1, 'user_id' => 1, 'value' => -1], ['guard' => false]);

		try {
			$this->Favorites->save($second, ['atomic' => false]);
			$this->fail('Expected duplicate-key violation on second insert.');
		} catch (DatabaseException) {
			// Expected: tightened unique index trips on the second row.
		} catch (PDOException) {
			// Some drivers wrap as PDOException — also acceptable.
		}

		$this->assertSame(1, $this->Favorites->find()->count(), 'Only the first row should have landed.');
	}

	/**
	 * @return void
	 */
	public function testRemove(): void {
		$favorites = $this->Favorites->find()->all()->toArray();
		$this->assertCount(1, $favorites);

		$result = $this->Favorites->remove('Posts', 1, 1);
		$this->assertSame(1, $result);

		$favorites = $this->Favorites->find()->all()->toArray();
		$this->assertCount(0, $favorites);

		$result = $this->Favorites->remove('X', 1, 1);
		$this->assertSame(0, $result);

		$favorites = $this->Favorites->find()->all()->toArray();
		$this->assertCount(0, $favorites);
	}

	/**
	 * @return void
	 */
	public function testRemoveWithUuidForeignKey(): void {
		$uuid = '550e8400-e29b-41d4-a716-446655440000';

		$this->withStringForeignKeyColumn(function () use ($uuid): void {
			$favorite = $this->Favorites->newEmptyEntity();
			$favorite->patch(['model' => 'UuidPosts', 'foreign_key' => $uuid, 'user_id' => 1], ['guard' => false]);
			$this->Favorites->saveOrFail($favorite);

			$result = $this->Favorites->remove('UuidPosts', $uuid, 1);

			$this->assertSame(1, $result);
			$this->assertSame(0, $this->Favorites->find()->where(['model' => 'UuidPosts', 'foreign_key' => $uuid])->count());
		});
	}

	/**
	 * @return void
	 */
	public function testDelete(): void {
		$favorites = $this->Favorites->find()->all()->toArray();
		$this->assertCount(1, $favorites);

		$result = $this->Favorites->delete($favorites[0]);
		$this->assertTrue($result);

		$favorites = $this->Favorites->find()->all()->toArray();
		$this->assertCount(0, $favorites);
	}

	/**
	 * Switch the favorites_favorites.foreign_key column to string for the duration
	 * of a UUID-specific test, then restore the default integer-backed schema.
	 *
	 * @param callable $callback
	 *
	 * @return void
	 */
	protected function withStringForeignKeyColumn(callable $callback): void {
		$connection = ConnectionManager::get('test');
		$driver = $connection->getDriver();
		$driverName = strtolower((new ReflectionClass($driver))->getShortName());

		$this->Favorites->getSchema()->setColumnType('foreign_key', 'string');
		$this->changeForeignKeyColumnToString($connection, $driverName);

		try {
			$callback();
		} finally {
			$this->Favorites->deleteAll(['model' => 'UuidPosts']);
			$this->changeForeignKeyColumnToInteger($connection, $driverName);
			$this->Favorites->getSchema()->setColumnType('foreign_key', 'integer');
		}
	}

	/**
	 * @param \Cake\Database\Connection $connection
	 * @param string $driverName
	 *
	 * @return void
	 */
	protected function changeForeignKeyColumnToString($connection, string $driverName): void {
		if (str_contains($driverName, 'mysql')) {
			$connection->execute('ALTER TABLE favorites_favorites MODIFY foreign_key VARCHAR(36) NOT NULL');

			return;
		}
		if (str_contains($driverName, 'postgres')) {
			$connection->execute('ALTER TABLE favorites_favorites ALTER COLUMN foreign_key TYPE VARCHAR(36)');

			return;
		}
		if (str_contains($driverName, 'sqlite')) {
			return;
		}
	}

	/**
	 * @param \Cake\Database\Connection $connection
	 * @param string $driverName
	 *
	 * @return void
	 */
	protected function changeForeignKeyColumnToInteger($connection, string $driverName): void {
		if (str_contains($driverName, 'mysql')) {
			$connection->execute('ALTER TABLE favorites_favorites MODIFY foreign_key INT UNSIGNED NOT NULL');

			return;
		}
		if (str_contains($driverName, 'postgres')) {
			$connection->execute('ALTER TABLE favorites_favorites ALTER COLUMN foreign_key TYPE INTEGER USING foreign_key::integer');

			return;
		}
		if (str_contains($driverName, 'sqlite')) {
			return;
		}
	}

}
