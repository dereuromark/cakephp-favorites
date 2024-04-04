<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\View\Helper;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Favorites\View\Helper\StarsHelper;

class StarsHelperTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var list<string>
	 */
	protected array $fixtures = [
		'plugin.Favorites.Posts',
		'plugin.Favorites.Users',
	];

	/**
	 * @var \Favorites\View\Helper\StarsHelper
	 */
	protected $Stars;

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		$view = new View();
		$this->Stars = new StarsHelper($view);

		Configure::write('Favorites.models.Posts', 'Posts');
		$this->loadRoutes();
	}

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		unset($this->Stars);

		parent::tearDown();

		Configure::delete('Favorites.models.Posts');
	}

	/**
	 * @uses \Favorites\View\Helper\StarsHelper::icon()
	 *
	 * @return void
	 */
	public function testIcon(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * @uses \Favorites\View\Helper\StarsHelper::linkIcon()
	 *
	 * @return void
	 */
	public function testLinkIcon(): void {
		Configure::write('Auth.User.id', 1);
		$this->Stars->setConfig('html', '<span class="fa-solid fa-star%s"%s></span>');

		$result = $this->Stars->linkIcon('Posts', 1);
		$this->assertStringStartsWith('<a href="#" onclick="document.post_', $result);
		$this->assertStringEndsWith('><span class="fa-solid fa-star" title="Click to star." style="color: #aaa"></span></a>', $result);
	}

	/**
	 * @uses \Favorites\View\Helper\StarsHelper::linkIcon()
	 *
	 * @return void
	 */
	public function testLinkIconStarred(): void {
		Configure::write('Auth.User.id', 1);
		$favorite = $this->getTableLocator()->get('Favorites.Favorites')->newEntity([
			'model' => 'Posts',
			'foreign_key' => 1,
			'user_id' => 1,
		]);
		$this->getTableLocator()->get('Favorites.Favorites')->saveOrFail($favorite);

		$result = $this->Stars->linkIcon('Posts', 1);
		$this->assertStringStartsWith('<a href="#" onclick="document.post_', $result);
		$this->assertStringEndsWith('><span class="star starred" title="Starred by you. Click to unstar." style="color: #ffa500">★</span></a>', $result);
	}

	/**
	 * @uses \Favorites\View\Helper\StarsHelper::urlStar()
	 *
	 * @return void
	 */
	public function testUrlStar(): void {
		$result = $this->Stars->urlStar('Posts', 1);
		$expected = [
			'plugin' => 'Favorites',
			'controller' => 'Stars',
			'action' => 'star',
			'Posts',
			1,
		];
		$this->assertEquals($expected, $result);
	}

	/**
	 * @uses \Favorites\View\Helper\StarsHelper::urlUnstar()
	 *
	 * @return void
	 */
	public function testUrlUnstar(): void {
		$result = $this->Stars->urlUnstar('Posts', 1);
		$expected = [
			'plugin' => 'Favorites',
			'controller' => 'Stars',
			'action' => 'unstar',
			'Posts',
			1,
		];
		$this->assertEquals($expected, $result);
	}

}
