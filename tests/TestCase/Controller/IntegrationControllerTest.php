<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @uses \Favorites\Controller\StarsController
 */
class IntegrationControllerTest extends TestCase {

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
	 * @uses \Favorites\Controller\StarsController::star()
	 *
	 * @return void
	 */
	public function testView(): void {
		$this->disableErrorHandlerMiddleware();

		Configure::write('Favorites.models.Posts', 'Posts');

		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->post(['controller' => 'StarredPosts', 'action' => 'view', 1]);

		$this->assertResponseOk();

		$this->assertResponseContains('<span class="star starred" title="Starred by you. Click to unstar." style="color: #ffa500">★</span>');

		Configure::delete('Favorites.models');
	}

	/**
	 * @uses \Favorites\Controller\StarsController::star()
	 *
	 * @return void
	 */
	public function testViewUnstarred(): void {
		$this->disableErrorHandlerMiddleware();

		Configure::write('Favorites.models.Posts', 'Posts');

		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->fetchTable('Favorites.Favorites')->deleteAll('1=1');

		$this->post(['controller' => 'StarredPosts', 'action' => 'view', 1]);

		$this->assertResponseOk();

		$this->assertResponseContains('<span class="star" title="Click to star." style="color: #aaa">★</span>');

		Configure::delete('Favorites.models');
	}

}
