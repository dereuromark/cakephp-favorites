<?php
declare(strict_types=1);

namespace Favorites\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Favorites\Controller\LikesController;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * Favorites\Controller\LikesController Test Case
 */
#[UsesClass(LikesController::class)]
class LikesControllerTest extends TestCase {

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
	 * @uses \Favorites\Controller\LikesController::like()
	 *
	 * @return void
	 */
	public function testLike(): void {
		$this->disableErrorHandlerMiddleware();
		$this->enableRetainFlashMessages();

		Configure::write('Favorites.models.Posts', 'Posts');

		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->post(['plugin' => 'Favorites', 'controller' => 'Likes', 'action' => 'like', 'Posts', 1]);

		$this->assertRedirect(['action' => 'index']);

		// Regression: prior code used `$result->isNew()` to detect failure,
		// but `findOrCreate` + `saveOrFail` always returns a non-new entity
		// — so on the success path the error message NEVER fires (correct
		// for the success case but means the error branch was unreachable
		// for the actual failure case). The hasErrors() switch makes the
		// guard meaningful again. Either way, a successful POST must
		// land a row and not leave a stale error flash sitting in session.
		$row = $this->fetchTable('Favorites.Favorites')->find()
			->where(['model' => 'Posts', 'foreign_key' => 1, 'user_id' => 1, 'value' => 1])
			->first();
		$this->assertNotNull($row, 'A like row must have landed in the DB.');

		$flash = $this->_requestSession->read('Flash.flash') ?? [];
		foreach ($flash as $message) {
			$this->assertNotSame(
				__d('favorites', 'Could not save like, please try again.'),
				$message['message'] ?? null,
				'A successful like must not flash a "could not save" error.',
			);
		}

	}

	/**
	 * Test add method
	 *
	 * @uses \Favorites\Controller\LikesController::dislike()
	 *
	 * @return void
	 */
	public function testDislike(): void {
		$this->disableErrorHandlerMiddleware();

		Configure::write('Favorites.models.Posts', 'Posts');

		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
				],
			],
		]);

		$this->post(['plugin' => 'Favorites', 'controller' => 'Likes', 'action' => 'dislike', 'Posts', 1]);

		$this->assertRedirect(['action' => 'index']);

	}

	/**
	 * @uses \Favorites\Controller\LikesController::remove()
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

		$this->delete(['plugin' => 'Favorites', 'controller' => 'Likes', 'action' => 'remove', 'Posts', $favorite->id]);

		$this->assertRedirect(['action' => 'index']);

	}

	/**
	 * @uses \Favorites\Controller\LikesController::delete()
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

		$this->delete(['plugin' => 'Favorites', 'controller' => 'Likes', 'action' => 'delete', $favorite->id]);

		$this->assertRedirect(['action' => 'index']);
	}

	public function testTypeSpecificAliasStoresTheAlias(): void {
		Configure::write('Favorites.models', ['like' => ['Articles' => 'Posts']]);
		$this->fetchTable('Favorites.Favorites')->deleteAll([]);
		$this->session(['Auth.User.id' => 1]);
		$this->post(['plugin' => 'Favorites', 'controller' => 'Likes', 'action' => 'like', 'Articles', 1]);
		$this->assertRedirect();
		$favorite = $this->fetchTable('Favorites.Favorites')->find()->where(['model' => 'Articles'])->firstOrFail();
		$this->assertSame(1, $favorite->foreign_key);
		$this->assertSame(1, $favorite->value);
	}

	public function testLegacyTableAliasIsUpdatedWithoutDuplicate(): void {
		Configure::write('Favorites.models', ['like' => ['Articles' => 'Posts']]);
		$this->session(['Auth.User.id' => 1]);
		$this->post(['plugin' => 'Favorites', 'controller' => 'Likes', 'action' => 'like', 'Articles', 1]);
		$this->assertRedirect();
		$favorites = $this->fetchTable('Favorites.Favorites');
		$this->assertSame(0, $favorites->find()->where(['model' => 'Articles'])->count());
		$this->assertSame(1, $favorites->find()->where(['model' => 'Posts', 'foreign_key' => 1, 'user_id' => 1])->count());
	}

}
