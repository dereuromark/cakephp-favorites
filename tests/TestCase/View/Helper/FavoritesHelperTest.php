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

	/**
	 * @return void
	 */
	public function testWidgetRendersIconMarkup(): void {
		$this->Favorites = $this->getMockBuilder(FavoritesHelper::class)
			->setConstructorArgs([new View(), ['icons' => [1 => '<span class="custom-icon">Yes</span>'], 'resetIcon' => '<span class="reset-icon">Reset</span>']])
			->onlyMethods(['value'])
			->getMock();
		$this->Favorites->expects($this->exactly(2))->method('value')->willReturn(null);
		$html = $this->Favorites->widget('Posts', 1);
		$this->assertStringContainsString('<span class="custom-icon">Yes</span>', $html);
		$this->assertStringNotContainsString('&lt;span', $html);
		$html = $this->Favorites->widget('Posts', 1, 1);
		$this->assertStringContainsString('<span class="reset-icon">Reset</span>', $html);
		$this->assertStringNotContainsString('&lt;span', $html);
	}

}
