<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Event\EventManager;
use Cake\Http\MiddlewareQueue;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Favorites\Controller\Admin\FavoritesController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[UsesClass(FavoritesController::class)]
class FavoritesControllerTest extends TestCase {

	use IntegrationTestTrait;

	/**
	 * @var list<string>
	 */
	protected array $fixtures = [
		'plugin.Favorites.Favorites',
	];

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		Configure::write('Favorites.models', ['Threads' => 'Threads']);
		// The plugin's beforeFilter requires `Favorites.adminAccess` to be a Closure
		// returning true for permitted callers. Tests run as a permitted caller by
		// default; individual tests can override the gate to assert the deny path.
		Configure::write('Favorites.adminAccess', function ($request): bool {
			return true;
		});
	}

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		Configure::delete('Favorites.adminAccess');

		parent::tearDown();
	}

	/**
	 * @uses \Favorites\Controller\Admin\FavoritesController::index()
	 *
	 * @return void
	 */
	public function testIndex(): void {
		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->get(['prefix' => 'Admin', 'plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'index']);

		$this->assertResponseOk();
	}

	/**
	 * @uses \Favorites\Controller\Admin\FavoritesController::listing()
	 *
	 * @return void
	 */
	public function testListing(): void {
		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->get(['prefix' => 'Admin', 'plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'listing']);

		$this->assertResponseOk();
	}

	/**
	 * @uses \Favorites\Controller\Admin\FavoritesController::delete()
	 *
	 * @return void
	 */
	public function testDelete(): void {
		$this->post(['prefix' => 'Admin', 'plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'delete', 1]);

		$this->assertRedirect(['prefix' => 'Admin', 'plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'index']);
	}

	public function testResetAllowsATypeSpecificModelAlias(): void {
		Configure::write('Favorites.models', ['star' => ['Posts' => 'Posts']]);
		$this->post(['prefix' => 'Admin', 'plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'index', '?' => ['model' => 'Posts']]);
		$this->assertRedirect();
		$this->assertSame(0, $this->fetchTable('Favorites.Favorites')->find()->count());
	}

	public function testResetAllowsConfiguredStorageTarget(): void {
		Configure::write('Favorites.models', ['star' => ['Articles' => 'Posts']]);
		$this->post(['prefix' => 'Admin', 'plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'index', '?' => ['model' => 'Posts']]);
		$this->assertRedirect();
		$this->assertSame(0, $this->fetchTable('Favorites.Favorites')->find()->count());
	}

	/**
	 * CakePHP's CspMiddleware stores the nonce as `cspScriptNonce`.
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	#[DataProvider('cspActionProvider')]
	public function testCspScriptNonce(string $action): void {
		$this->withRequestAttribute('cspScriptNonce', 'core-nonce');

		$this->get(['prefix' => 'Admin', 'plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => $action]);

		$this->assertResponseOk();
		$this->assertResponseContains('<script nonce="core-nonce">');
		$this->assertResponseNotRegExp('/<script(?![^>]*nonce=)/');
	}

	/**
	 * @return array<string, array<string>>
	 */
	public static function cspActionProvider(): array {
		return [
			'index' => ['index'],
			'listing' => ['listing'],
		];
	}

	/**
	 * A custom middleware may still provide the nonce as `cspNonce`.
	 *
	 * @return void
	 */
	public function testCspNonceFallback(): void {
		$this->withRequestAttribute('cspNonce', 'custom-nonce');

		$this->get(['prefix' => 'Admin', 'plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => 'index']);

		$this->assertResponseOk();
		$this->assertResponseContains('<script nonce="custom-nonce">');
		$this->assertResponseNotRegExp('/<script(?![^>]*nonce=)/');
	}

	/**
	 * Adds a middleware that sets a request attribute, like a CSP middleware would.
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return void
	 */
	protected function withRequestAttribute(string $name, string $value): void {
		EventManager::instance()->on('Server.buildMiddleware', function (EventInterface $event, MiddlewareQueue $queue) use ($name, $value): void {
			$queue->prepend(function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($name, $value): ResponseInterface {
				return $handler->handle($request->withAttribute($name, $value));
			});
		});
	}

}
