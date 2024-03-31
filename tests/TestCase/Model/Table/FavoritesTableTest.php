<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use Favorites\Model\Table\FavoritesTable;

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
	public function testDelete(): void {
		$favorites = $this->Favorites->find()->all()->toArray();
		$this->assertCount(1, $favorites);

		$result = $this->Favorites->delete($favorites[0]);
		$this->assertTrue($result);

		$favorites = $this->Favorites->find()->all()->toArray();
		$this->assertCount(0, $favorites);
	}

}
