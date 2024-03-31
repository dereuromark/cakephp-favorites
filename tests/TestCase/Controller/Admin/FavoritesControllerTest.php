<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Controller\Admin;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @uses \Favorites\Controller\Admin\FavoritesController
 */
class FavoritesControllerTest extends TestCase {

	use IntegrationTestTrait;

	/**
	 * @var list<string>
	 */
	protected array $fixtures = [
		'plugin.Favorites.Favorites',
	];

	/**
	 * @uses \Favorites\Controller\Admin\FavoritesController::index()
	 *
	 * @return void
	 */
	public function testIndex(): void {
		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->get(['prefix' => 'Admin', 'plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'index']);

		$this->assertResponseOk();
	}

	/**
	 * @uses \Favorites\Controller\Admin\FavoritesController::delete()
	 *
	 * @return void
	 */
	public function testDelete(): void {
		$this->post(['prefix' => 'Admin', 'plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'delete', 1]);

		$this->assertRedirect(['prefix' => 'Admin', 'plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'index']);
	}

}
