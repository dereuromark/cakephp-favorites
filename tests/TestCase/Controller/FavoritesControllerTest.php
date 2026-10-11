<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Favorites\Controller\FavoritesController;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * Favorites\Controller\FavoritesController Test Case
 */
#[UsesClass(FavoritesController::class)]
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

	protected function tearDown(): void {
		Configure::delete('Favorites.models');
		parent::tearDown();
	}

	/**
	 * Test add method
	 *
	 * @uses \Favorites\Controller\FavoritesController::add()
	 *
	 * @return void
	 */
	public function testAdd(): void {
		$this->disableErrorHandlerMiddleware();

		Configure::write('Favorites.models.Posts', 'Posts');

		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->post(['plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'add', 'Posts', 1], ['value' => '1']);

		$this->assertRedirect(['action' => 'index']);

	}

	/**
	 * @uses \Favorites\Controller\FavoritesController::remove()
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

		$this->delete(['plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'remove', 'Posts', $favorite->id]);

		$this->assertRedirect(['action' => 'index']);

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

	public function testTypeSpecificAliasStoresTheAlias(): void {
		Configure::write('Favorites.models', ['favorite' => ['Articles' => 'Posts']]);
		$this->session(['Auth.User.id' => 1]);
		$this->post(['plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'add', 'Articles', 1], ['value' => 4]);
		$this->assertRedirect();
		$favorite = $this->fetchTable('Favorites.Favorites')->find()->where(['model' => 'Articles'])->firstOrFail();
		$this->assertSame(1, $favorite->foreign_key);
		$this->assertSame(4, $favorite->value);
	}

}
