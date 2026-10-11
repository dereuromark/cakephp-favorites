<?php

declare(strict_types=1);

namespace Favorites\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class ResetStorageTest extends TestCase {

	use IntegrationTestTrait;

	/**
	 * @var list<string>
	 */
	protected array $fixtures = ['plugin.Favorites.Favorites', 'plugin.Favorites.Users'];

	protected function setUp(): void {
		parent::setUp();
		Configure::write('Favorites.adminAccess', fn (): bool => true);
	}

	protected function tearDown(): void {
		Configure::delete('Favorites');
		parent::tearDown();
	}

	public function testResetIncludesEveryTargetForSharedTypeSpecificAlias(): void {
		Configure::write('Favorites.models', [
			'star' => ['Posts' => 'StarPosts'],
			'like' => ['Posts' => 'LikePosts'],
		]);
		$favorites = $this->fetchTable('Favorites.Favorites');
		$favorites->add('StarPosts', 1, 1);
		$favorites->add('LikePosts', 1, 1, 1);
		$this->post(['prefix' => 'Admin', 'plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'index', '?' => ['model' => 'LikePosts']]);
		$this->assertRedirect();
		$this->assertSame(0, $favorites->find()->where(['model' => 'LikePosts'])->count());
		$this->assertSame(1, $favorites->find()->where(['model' => 'StarPosts'])->count());
	}

	public function testListingWithoutUsernameUsesUserIdAsLinkLabel(): void {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'listing']);
		$this->assertResponseOk();
		$this->assertResponseContains('/admin/favorites/Users/view/1">1</a>');
	}

}
