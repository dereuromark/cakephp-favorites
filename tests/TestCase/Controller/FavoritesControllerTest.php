<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Favorites\Controller\FavoritesController Test Case
 *
 * @uses \Favorites\Controller\FavoritesController
 */
class FavoritesControllerTest extends TestCase {

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
	 * @uses \Favorites\Controller\FavoritesController::add()
	 *
	 * @return void
	 */
	public function testAdd(): void {
		$this->disableErrorHandlerMiddleware();

		Configure::write('Favorites.controllerModels.Posts', 'Posts');

		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->post(['plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'add', 'Posts', 1]);

		$this->assertRedirect(['action' => 'index']);

		Configure::delete('Favorites.controllerModels');
	}

	/**
	 * @uses \Favorites\Controller\FavoritesController::delete()
	 *
	 * @return void
	 */
	public function testRemove(): void {
		$favorite = $this->fetchTable('Favorites.Favorites')->find()->firstOrFail();

		Configure::write('Favorites.controllerModels.Posts', 'Posts');

		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->delete(['plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'remove', 'Posts', $favorite->id]);

		$this->assertRedirect(['action' => 'index']);

		Configure::delete('Favorites.controllerModels');
	}

	/**
	 * @uses \Favorites\Controller\FavoritesController::delete()
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

		$this->delete(['plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'delete', $favorite->id]);

		$this->assertRedirect(['action' => 'index']);
	}

}
