<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
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
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		Configure::write('Favorites.models', ['Threads' => 'Threads']);
		// The plugin's beforeFilter requires `Favorites.adminAccess` to be a Closure
		// returning true for permitted callers. Tests run as a permitted caller by
		// default; individual tests can override the gate to assert the deny path.
		Configure::write('Favorites.adminAccess', function ($request): bool {
			return true;
		});
	}

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		Configure::delete('Favorites.adminAccess');

		parent::tearDown();
	}

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
	 * @uses \Favorites\Controller\Admin\FavoritesController::listing()
	 *
	 * @return void
	 */
	public function testListing(): void {
		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->get(['prefix' => 'Admin', 'plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'listing']);

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
