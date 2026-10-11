<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Favorites\Controller\StarsController;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * Favorites\Controller\StarsController Test Case
 */
#[UsesClass(StarsController::class)]
class StarsControllerTest extends TestCase {

	use IntegrationTestTrait;

	/**
	 * Fixtures
	 *
	 * @var list<string>
	 */
	protected array $fixtures = [
		'plugin.Favorites.Favorites',
		'plugin.Favorites.Posts',
		'plugin.Favorites.Users',
	];

	protected function tearDown(): void {
		Configure::delete('Favorites.models');
		parent::tearDown();
	}

	/**
	 * Test add method
	 *
	 * @uses \Favorites\Controller\StarsController::star()
	 *
	 * @return void
	 */
	public function testStar(): void {
		$this->disableErrorHandlerMiddleware();

		Configure::write('Favorites.models.Posts', 'Posts');

		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->post(['plugin' => 'Favorites', 'controller' => 'Stars', 'action' => 'star', 'Posts', 1]);

		$this->assertRedirect(['action' => 'index']);

	}

	/**
	 * @uses \Favorites\Controller\StarsController::unstar()
	 *
	 * @return void
	 */
	public function testUnstar(): void {
		$favorite = $this->fetchTable('Favorites.Favorites')->find()->firstOrFail();

		Configure::write('Favorites.models.Posts', 'Posts');

		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->delete(['plugin' => 'Favorites', 'controller' => 'Stars', 'action' => 'unstar', 'Posts', $favorite->id]);

		$this->assertRedirect(['action' => 'index']);

	}

	/**
	 * @uses \Favorites\Controller\StarsController::delete()
	 *
	 * @return void
	 */
	public function testDelete(): void {
		$favorite = $this->fetchTable('Favorites.Favorites')->find()->firstOrFail();

		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->delete(['plugin' => 'Favorites', 'controller' => 'Stars', 'action' => 'delete', $favorite->id]);

		$this->assertRedirect(['action' => 'index']);
	}

	public function testTypeSpecificAliasKeepsConfiguredStorageModel(): void {
		Configure::write('Favorites.models', ['star' => ['Articles' => 'Posts']]);
		$this->fetchTable('Favorites.Favorites')->deleteAll([]);
		$this->session(['Auth.User.id' => 1]);
		$this->post(['plugin' => 'Favorites', 'controller' => 'Stars', 'action' => 'star', 'Articles', 1]);
		$this->assertRedirect();
		$favorite = $this->fetchTable('Favorites.Favorites')->find()->where(['model' => 'Posts'])->firstOrFail();
		$this->assertSame(1, $favorite->foreign_key);
	}

}
