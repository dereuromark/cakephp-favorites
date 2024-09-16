<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;

class LikeableBehaviorTest extends TestCase {

	/**
	 * @var \Cake\ORM\Table&\Favorites\Model\Behavior\LikeableBehavior
	 */
	protected $table;

	/**
	 * Fixtures
	 *
	 * @var list<string>
	 */
	protected array $fixtures = [
		'plugin.Favorites.Favorites',
		'plugin.Favorites.Users',
		'plugin.Favorites.Posts',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->table = $this->getTableLocator()->get('Posts');
		$this->table->addBehavior('Favorites.Likeable');
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		$this->table->removeBehavior('Likeable');
		unset($this->table);

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testLike(): void {
		$options = [
			'modelId' => 1,
			'userId' => 1,
		];
		$result = $this->table->addLike($options);

		/** @var \Favorites\Model\Entity\Favorite $favorite */
		$favorite = $this->table->Favorites->get($result);
		$this->assertSame('Posts', $favorite->model);
		$this->assertSame(1, $favorite->value);

		$favorites = $this->table->Favorites->find()->all()->toArray();
		$this->assertCount(1, $favorites);
	}

	/**
	 * @return void
	 */
	public function testDislike(): void {
		$options = [
			'modelId' => 1,
			'userId' => 1,
		];
		$result = $this->table->addDislike($options);

		/** @var \Favorites\Model\Entity\Favorite $favorite */
		$favorite = $this->table->Favorites->get($result);
		$this->assertSame('Posts', $favorite->model);
		$this->assertSame(-1, $favorite->value);

		$favorites = $this->table->Favorites->find()->all()->toArray();
		$this->assertCount(1, $favorites);
	}

}
