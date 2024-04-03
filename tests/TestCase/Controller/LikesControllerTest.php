<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Favorites\Controller\LikesController Test Case
 *
 * @uses \Favorites\Controller\LikesController
 */
class LikesControllerTest extends TestCase {

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
	 * @uses \Favorites\Controller\LikesController::like()
	 *
	 * @return void
	 */
	public function testLike(): void {
		$this->disableErrorHandlerMiddleware();

		Configure::write('Favorites.models.Posts', 'Posts');

		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->post(['plugin' => 'Favorites', 'controller' => 'Likes', 'action' => 'like', 'Posts', 1]);

		$this->assertRedirect(['action' => 'index']);

		Configure::delete('Favorites.models');
	}

	/**
	 * Test add method
	 *
	 * @uses \Favorites\Controller\LikesController::dislike()
	 *
	 * @return void
	 */
	public function testDislike(): void {
		$this->disableErrorHandlerMiddleware();

		Configure::write('Favorites.models.Posts', 'Posts');

		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->post(['plugin' => 'Favorites', 'controller' => 'Likes', 'action' => 'dislike', 'Posts', 1]);

		$this->assertRedirect(['action' => 'index']);

		Configure::delete('Favorites.models');
	}

	/**
	 * @uses \Favorites\Controller\LikesController::remove()
	 *
	 * @return void
	 */
	public function testRemove(): void {
		$favorite = $this->fetchTable('Favorites.Favorites')->find()->firstOrFail();

		Configure::write('Favorites.models.Posts', 'Posts');

		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->delete(['plugin' => 'Favorites', 'controller' => 'Likes', 'action' => 'remove', 'Posts', $favorite->id]);

		$this->assertRedirect(['action' => 'index']);

		Configure::delete('Favorites.models');
	}

	/**
	 * @uses \Favorites\Controller\LikesController::delete()
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

		$this->delete(['plugin' => 'Favorites', 'controller' => 'Likes', 'action' => 'delete', $favorite->id]);

		$this->assertRedirect(['action' => 'index']);
	}

}
