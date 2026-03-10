<?php

declare(strict_types=1);

namespace Favorites\Test\TestCase\View\Helper;

use Cake\Core\Configure;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Favorites\View\Helper\LikesHelper;

/**
 * @uses \Favorites\View\Helper\LikesHelper
 */
class LikesHelperTest extends TestCase {

	/**
	 * @var \Favorites\View\Helper\LikesHelper
	 */
	protected LikesHelper $Likes;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		Router::createRouteBuilder('/')->scope('/', function (RouteBuilder $routes) {
			$routes->setRouteClass(DashedRoute::class);
			$routes->plugin('Favorites', ['path' => '/favorites'], function (RouteBuilder $builder) {
				$builder->fallbacks();
			});
		});

		$view = new View();
		$this->Likes = new LikesHelper($view);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		unset($this->Likes);
		Configure::delete('Favorites');
		Router::reload();

		parent::tearDown();
	}

	/**
	 * Test HTML type constants
	 *
	 * @return void
	 */
	public function testHtmlTypeConstants(): void {
		$this->assertSame(0, LikesHelper::HTML_TYPE_UTF8);
		$this->assertSame(1, LikesHelper::HTML_TYPE_FA6);
	}

	/**
	 * Test initialize with default UTF8 icons
	 *
	 * @return void
	 */
	public function testInitializeDefaultIcons(): void {
		$html = $this->Likes->getConfig('html');
		$this->assertIsArray($html);
		$this->assertSame('👍', $html[1]);
		$this->assertSame('👎', $html[-1]);
		$this->assertSame('❌', $html[0]);
	}

	/**
	 * Test initialize with FA6 icons
	 *
	 * @return void
	 */
	public function testInitializeWithFa6Icons(): void {
		$view = new View();
		$helper = new LikesHelper($view, ['html' => LikesHelper::HTML_TYPE_FA6]);

		$html = $helper->getConfig('html');
		$this->assertIsArray($html);
		$this->assertStringContainsString('fa-arrow-up', $html[1]);
		$this->assertStringContainsString('fa-arrow-down', $html[-1]);
		$this->assertStringContainsString('fa-remove', $html[0]);
	}

	/**
	 * Test urlLike generates correct URL
	 *
	 * @return void
	 */
	public function testUrlLike(): void {
		$url = $this->Likes->urlLike('Posts', 123);
		$this->assertStringContainsString('/favorites/likes/like/Posts/123', $url);
	}

	/**
	 * Test urlDislike generates correct URL
	 *
	 * @return void
	 */
	public function testUrlDislike(): void {
		$url = $this->Likes->urlDislike('Posts', 456);
		$this->assertStringContainsString('/favorites/likes/dislike/Posts/456', $url);
	}

	/**
	 * Test icon returns correct HTML for like value
	 *
	 * @return void
	 */
	public function testIconLike(): void {
		$icon = $this->Likes->icon('Posts', 1, 1);
		$this->assertSame('👍', $icon);
	}

	/**
	 * Test icon returns correct HTML for dislike value
	 *
	 * @return void
	 */
	public function testIconDislike(): void {
		$icon = $this->Likes->icon('Posts', 1, -1);
		$this->assertSame('👎', $icon);
	}

	/**
	 * Test icon returns correct HTML for remove/reset value
	 *
	 * @return void
	 */
	public function testIconRemove(): void {
		$icon = $this->Likes->icon('Posts', 1, 0);
		$this->assertSame('❌', $icon);
	}

}
