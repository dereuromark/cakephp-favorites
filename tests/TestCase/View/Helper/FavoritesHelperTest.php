<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\View\Helper;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Favorites\View\Helper\FavoritesHelper;

class FavoritesHelperTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \Favorites\View\Helper\FavoritesHelper
	 */
	protected $Favorites;

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		$view = new View();
		$this->Favorites = new FavoritesHelper($view);

		Configure::write('Favorites.models.Posts', 'Posts');
		$this->loadRoutes();
	}

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		unset($this->Favorites);

		parent::tearDown();

		Configure::delete('Favorites.models.Posts');
	}

	/**
	 * @uses \Favorites\View\Helper\FavoritesHelper::urlAdd()
	 *
	 * @return void
	 */
	public function testUrlAdd(): void {
		$result = $this->Favorites->urlAdd('Posts', 1);
		$expected = [
			'plugin' => 'Favorites',
			'controller' => 'Favorites',
			'action' => 'add',
			'Posts',
			1,
		];
		$this->assertEquals($expected, $result);
	}

	/**
	 * @uses \Favorites\View\Helper\FavoritesHelper::urlRemove()
	 *
	 * @return void
	 */
	public function testUrlRemove(): void {
		$result = $this->Favorites->urlRemove('Posts', 1);
		$expected = [
			'plugin' => 'Favorites',
			'controller' => 'Favorites',
			'action' => 'remove',
			'Posts',
			1,
		];
		$this->assertEquals($expected, $result);
	}

}
