<?php

namespace TestApp;

use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\RouteBuilder;

class Application extends BaseApplication {

	/**
	 * @inheritDoc
	 */
	public function bootstrap(): void {
		$this->addPlugin('Favorites');
	}

	/**
	 * @param \Cake\Routing\RouteBuilder $routes
	 *
	 * @return void
	 */
	public function routes(RouteBuilder $routes): void {
		$routes->plugin(
			'Favorites',
			['path' => '/favorites'],
			function (RouteBuilder $builder): void {
				$builder->connect('/', ['controller' => 'Favorites', 'action' => 'index']);

				$builder->fallbacks();
			}
		);

		$routes->prefix('Admin', function (RouteBuilder $builder): void {
			$builder->plugin(
				'Favorites',
				['path' => '/favorites'],
				function (RouteBuilder $builder): void {
					$builder->connect('/', ['controller' => 'Favorites', 'action' => 'index']);

					$builder->fallbacks();
				},
			);
		});
	}

	/**
	 * @param \Cake\Http\MiddlewareQueue $middlewareQueue
	 *
	 * @return \Cake\Http\MiddlewareQueue
	 */
	public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue {
		$middlewareQueue->add(new RoutingMiddleware($this));

		return $middlewareQueue;
	}

}
