<?php

declare(strict_types=1);

namespace Favorites\Test\TestCase\Controller\Component;

use ArrayObject;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class StorageAliasTest extends TestCase {

	/**
	 * @var list<string>
	 */
	protected array $fixtures = ['plugin.Favorites.Favorites', 'plugin.Favorites.Users', 'plugin.Favorites.Posts'];

	/**
	 * @return array<string, array<string>>
	 */
	public static function components(): array {
		return [
			'favorite' => ['Favoriteable', 'add'],
			'like' => ['Likeable', 'like'],
			'star' => ['Starable', 'star'],
		];
	}

	/**
	 * @param string $componentName
	 * @param string $action
	 *
	 * @return void
	 */
	#[DataProvider('components')]
	public function testPostedAliasRetainsReleasedStorageWithoutModelMap(string $componentName, string $action): void {
		$this->process($componentName, $action, 'WishlistedPosts');
		$favorites = $this->fetchTable('Favorites.Favorites');
		$this->assertSame(1, $favorites->find()->where(['model' => 'WishlistedPosts', 'foreign_key' => 987654])->count());
		$this->assertSame(0, $favorites->find()->where(['model' => 'Posts', 'foreign_key' => 987654])->count());
	}

	/**
	 * @param string $componentName
	 * @param string $action
	 * @param mixed $alias
	 *
	 * @return void
	 */
	protected function process(string $componentName, string $action, mixed $alias): void {
		Configure::delete('Favorites.models');
		$request = new ServerRequest([
			'url' => '/posts/view/1',
			'environment' => ['REQUEST_METHOD' => 'POST'],
			'params' => ['controller' => 'Posts', 'action' => 'view', 'pass' => []],
			'post' => ['favorite' => 1, 'alias' => $alias, 'action' => $action, 'id' => 987654, 'value' => 4],
		]);
		$request = $request->withAttribute('identity', new ArrayObject(['id' => 1]));
		$controller = new class ($request) extends Controller {

			public Table $Posts;

			protected ?string $defaultTable = 'Posts';

		};
		$controller->Posts = $this->fetchTable('Posts');
		$component = $controller->loadComponent('Favorites.' . $componentName, ['on' => 'startup', 'useEntity' => false]);
		if (method_exists($component, 'beforeFilter')) {
			$component->beforeFilter(new Event('Controller.beforeFilter', $controller));
		}
		$component->startup(new Event('Controller.startup', $controller));
	}

}
