<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class LikesIntegrationControllerTest extends TestCase {

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
	 * @uses \TestApp\Controller\LikedPostsController::view()
	 *
	 * @return void
	 */
	public function testView(): void {
		$this->disableErrorHandlerMiddleware();

		Configure::write('Favorites.models.LikedPosts', 'Posts');

		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->get(['controller' => 'LikedPosts', 'action' => 'view', 1]);

		$this->assertResponseOk();
		$this->assertResponseContains('👍');
		$this->assertResponseContains('👎');

		Configure::delete('Favorites.models');
	}

	/**
	 * @uses \TestApp\Controller\LikedPostsController::view()
	 *
	 * @return void
	 */
	public function testViewLiked(): void {
		$this->disableErrorHandlerMiddleware();

		Configure::write('Favorites.models.LikedPosts', 'Posts');

		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$result = $this->fetchTable('Favorites.Favorites')->add('LikedPosts', 1, 1, 1);
		$this->assertEmpty($result->getErrors());

		$this->get(['controller' => 'LikedPosts', 'action' => 'view', 1]);

		$this->assertResponseOk();
		//dd((string)$this->_response->getBody());
		//$this->assertResponseContains('<span class="star" title="Click to star." style="color: #aaa">★</span>');

		Configure::delete('Favorites.models');
	}

}
