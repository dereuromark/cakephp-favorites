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

	protected function tearDown(): void {
		Configure::delete('Favorites');
		parent::tearDown();
	}

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

	}

	/**
	 * @return void
	 */
	public function testActionComponentKeepsThePostedStorageModel(): void {
		$this->disableErrorHandlerMiddleware();
		Configure::write('Favorites.models', ['WishlistedPosts' => 'Posts']);
		Configure::write('Favorites.useEntity', false);
		$this->session(['Auth.User.id' => 1]);
		$this->post(['controller' => 'LikedPosts', 'action' => 'view', 1], [
			'favorite' => 1,
			'alias' => 'WishlistedPosts',
			'action' => 'like',
			'id' => 1,
		]);
		$this->assertRedirect();
		$favorites = $this->fetchTable('Favorites.Favorites');
		$this->assertSame(1, $favorites->find()->where(['model' => 'WishlistedPosts', 'value' => 1])->count());
		$this->assertSame(1, $favorites->find()->where(['model' => 'Posts', 'value IS' => null])->count());
	}

}
