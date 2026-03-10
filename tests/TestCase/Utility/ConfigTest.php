<?php

declare(strict_types=1);

namespace Favorites\Test\TestCase\Utility;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Favorites\Utility\Config;
use InvalidArgumentException;

/**
 * @uses \Favorites\Utility\Config
 */
class ConfigTest extends TestCase {

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		Configure::delete('Favorites.models');

		parent::tearDown();
	}

	/**
	 * Test type constants
	 *
	 * @return void
	 */
	public function testTypeConstants(): void {
		$this->assertSame('star', Config::TYPE_STAR);
		$this->assertSame('like', Config::TYPE_LIKE);
		$this->assertSame('favorite', Config::TYPE_FAVORITE);
	}

	/**
	 * Test strategy constants
	 *
	 * @return void
	 */
	public function testStrategyConstants(): void {
		$this->assertSame('controller', Config::STRATEGY_CONTROLLER);
		$this->assertSame('action', Config::STRATEGY_ACTION);
	}

	/**
	 * Test strategy with valid controller strategy
	 *
	 * @return void
	 */
	public function testStrategyWithController(): void {
		$result = Config::strategy(Config::STRATEGY_CONTROLLER);
		$this->assertSame('controller', $result);
	}

	/**
	 * Test strategy with valid action strategy
	 *
	 * @return void
	 */
	public function testStrategyWithAction(): void {
		$result = Config::strategy(Config::STRATEGY_ACTION);
		$this->assertSame('action', $result);
	}

	/**
	 * Test strategy with invalid value throws exception
	 *
	 * @return void
	 */
	public function testStrategyWithInvalidValue(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid strategy invalid');

		Config::strategy('invalid');
	}

	/**
	 * Test alias finds model key from config
	 *
	 * @return void
	 */
	public function testAlias(): void {
		Configure::write('Favorites.models', [
			'Posts' => 'Blog.Posts',
			'Articles' => 'App.Articles',
		]);

		$result = Config::alias('Blog.Posts', Config::TYPE_FAVORITE);
		$this->assertSame('Posts', $result);

		$result = Config::alias('App.Articles', Config::TYPE_FAVORITE);
		$this->assertSame('Articles', $result);
	}

	/**
	 * Test alias returns null for non-existent model
	 *
	 * @return void
	 */
	public function testAliasNotFound(): void {
		Configure::write('Favorites.models', [
			'Posts' => 'Blog.Posts',
		]);

		$result = Config::alias('NonExistent', Config::TYPE_FAVORITE);
		$this->assertNull($result);
	}

	/**
	 * Test models returns config models
	 *
	 * @return void
	 */
	public function testModels(): void {
		Configure::write('Favorites.models', [
			'Posts' => 'Blog.Posts',
			'Articles' => 'App.Articles',
		]);

		$result = Config::models(Config::TYPE_FAVORITE);
		$this->assertArrayHasKey('Posts', $result);
		$this->assertArrayHasKey('Articles', $result);
	}

	/**
	 * Test models returns empty array when not configured
	 *
	 * @return void
	 */
	public function testModelsEmpty(): void {
		Configure::delete('Favorites.models');

		$result = Config::models(Config::TYPE_FAVORITE);
		$this->assertIsArray($result);
	}

	/**
	 * Test models with type-specific config
	 *
	 * @return void
	 */
	public function testModelsWithTypeSpecific(): void {
		Configure::write('Favorites.models', [
			Config::TYPE_STAR => [
				'StarPosts' => 'Blog.Posts',
			],
			'GeneralPosts' => 'App.Posts',
		]);

		$result = Config::models(Config::TYPE_STAR);
		$this->assertArrayHasKey('StarPosts', $result);
		$this->assertArrayHasKey('GeneralPosts', $result);
	}

}
