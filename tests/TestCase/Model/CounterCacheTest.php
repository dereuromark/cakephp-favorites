<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Model;

use Cake\TestSuite\TestCase;

class CounterCacheTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \Cake\ORM\Table&\Favorites\Model\Behavior\StarableBehavior
	 */
	protected $table;

	/**
	 * Fixtures
	 *
	 * @var list<string>
	 */
	protected array $fixtures = [
		'plugin.Favorites.Users',
		'plugin.Favorites.Posts',
		'plugin.Favorites.Favorites',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->table = $this->getTableLocator()->get('Posts');
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
	public function testCounter(): void {
		$this->table->addBehavior('Favorites.Starable', ['counterCache' => true, 'fieldCounter' => 'count']);

		$post = $this->table->find()->firstOrFail();

		/** @var \Favorites\Model\Behavior\StarableBehavior $behavior */
		$behavior = $this->table->getBehavior('Starable');
		$behavior->addStar(['modelId' => $post->id, 'userId' => 1]);

		$user = $this->getTableLocator()->get('Users')->newEntity([
			'name' => '2nd',
		]);
		$this->getTableLocator()->get('Users')->saveOrFail($user);
		$behavior->addStar(['modelId' => $post->id, 'userId' => $user->id]);

		$post = $this->table->find()
			->find('starred')
			->firstOrFail();
		$this->assertSame(2, $post->count);
	}

}
