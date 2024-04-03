<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Favorites\Controller\StarsController Test Case
 *
 * @uses \Favorites\Controller\StarsController
 */
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

		Configure::delete('Favorites.models');
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

		Configure::delete('Favorites.models');
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

}
