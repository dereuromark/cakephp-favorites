<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;

class FavoriteableBehaviorTest extends TestCase {

	/**
	 * @var \Cake\ORM\Table&\Favorites\Model\Behavior\FavoriteableBehavior
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
		$this->table->addBehavior('Favorites.Favoriteable');
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		unset($this->table);

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testAdd(): void {
		$options = [
			'value' => 1,
			'modelId' => 1,
			'userId' => 1,
		];
		/** @var \Favorites\Model\Behavior\FavoriteableBehavior $behavior */
		$behavior = $this->table->getBehavior('Favoriteable');
		$result = $behavior->addFavorite($options);

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
	public function testRemove(): void {
		$options = [
			'modelId' => 1,
			'userId' => 1,
		];
		/** @var \Favorites\Model\Behavior\FavoriteableBehavior $behavior */
		$behavior = $this->table->getBehavior('Favoriteable');
		$result = $behavior->removeFavorite($options);
		$this->assertSame(1, $result);

		$favorites = $this->table->Favorites->find()->all()->toArray();
		$this->assertCount(0, $favorites);
	}

	public function testFinderByUserUsesNamedArguments(): void {
		$this->table->Favorites->add('Posts', 1, 1, 1);
		$this->assertSame([1], $this->table->find('favoritesBy', userId: 1)->all()->extract('id')->toList());
		$this->assertSame([], $this->table->find('favoritesBy', userId: 2)->all()->extract('id')->toList());
	}

	public function testFavoritesFinderAcceptsNamedId(): void {
		$second = $this->table->newEntity(['title' => 'Another post', 'count' => 0]);
		$this->table->saveOrFail($second);
		$this->table->Favorites->add('Posts', 1, 1, 4);
		$this->table->Favorites->add('Posts', $second->get('id'), 1, 2);
		$posts = $this->table->find('favorites', id: 1)->orderBy(['Posts.id' => 'ASC'])->all()->toList();
		$this->assertCount(2, $posts);
		$this->assertCount(1, $posts[0]->get('favorites'));
		$this->assertSame(4, $posts[0]->get('favorites')[0]->get('value'));
		$this->assertCount(0, $posts[1]->get('favorites'));
	}

}
