<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;

class StarableBehaviorTest extends TestCase {

	/**
	 * @var \Cake\ORM\Table&\Favorites\Model\Behavior\StarableBehavior
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
		$this->table->addBehavior('Favorites.Starable');
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
	public function testLike(): void {
		$options = [
			'modelId' => 1,
			'userId' => 1,
		];
		/** @var \Favorites\Model\Behavior\StarableBehavior $behavior */
		$behavior = $this->table->getBehavior('Starable');
		$result = $behavior->addStar($options);

		/** @var \Favorites\Model\Entity\Favorite $favorite */
		$favorite = $this->table->Favorites->get($result);
		$this->assertSame('Posts', $favorite->model);
		$this->assertNull($favorite->value);

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
		/** @var \Favorites\Model\Behavior\StarableBehavior $behavior */
		$behavior = $this->table->getBehavior('Starable');
		$result = $behavior->removeStar($options);
		$this->assertSame(1, $result);

		$favorites = $this->table->Favorites->find()->all()->toArray();
		$this->assertCount(0, $favorites);
	}

	/**
	 * Regression: the `Starred` association must be attached even when the
	 * behavior is loaded without a `userId` config (the normal case from
	 * `Table::initialize()`). Without it, controllers and templates that
	 * want `->contain('Starred')` would error out with "association not
	 * found".
	 *
	 * @return void
	 */
	public function testStarredAssociationAvailableWithoutUserIdConfig(): void {
		$this->assertTrue(
			$this->table->hasAssociation('Starred'),
			'Starred hasOne must exist even when addBehavior() was called with no userId.',
		);

		// (No `set` call needed; the fixture provides a row.)
		// Association is usable: containment query doesn't blow up and
		// returns rows scoped to the requested user via overlaid conditions.
		// The fixture already provides a Favorite for user 1 on Post 1, so
		// no extra setup is needed.
		$row = $this->table->find()
			->contain(['Starred' => fn ($q) => $q->where(['Starred.user_id' => 1])])
			->where(['Posts.id' => 1])
			->first();

		$this->assertNotNull($row);
		$this->assertNotNull($row->starred, 'Starred must hydrate from the fixture row.');
	}

}
