<?php

declare(strict_types=1);

namespace Favorites\Test\TestCase\Utility;

use ArrayObject;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Favorites\Controller\Component\FavoriteableComponent;
use Favorites\Controller\Component\LikeableComponent;
use Favorites\Controller\Component\StarableComponent;
use Favorites\Controller\StarsController;
use Favorites\View\Helper\StarsHelper;
use ReflectionMethod;

class IdentityTest extends TestCase {

	protected function tearDown(): void {
		Configure::delete('Auth.User');
		parent::tearDown();
	}

	public function testIdentityIdsAreValidatedAcrossControllersHelpersAndComponents(): void {
		foreach ([1, '1', '1e4f8400-e29b-41d4-a716-446655440000', '1.5', '-1', '999999999999999999999999'] as $value) {
			$request = (new ServerRequest())->withAttribute('identity', new ArrayObject(['id' => $value]));
			$controller = new Controller($request);
			$registry = new ComponentRegistry($controller);
			$objects = [
				new StarsController($request),
				new StarsHelper(new View($request)),
				new StarableComponent($registry),
				new LikeableComponent($registry),
				new FavoriteableComponent($registry),
			];
			Configure::write('Auth.User.id', 2);
			foreach ($objects as $object) {
				$method = new ReflectionMethod($object, 'userId');
				$this->assertSame($value === 1 || $value === '1' ? 1 : null, $method->invoke($object), $object::class);
			}
		}
	}

}
