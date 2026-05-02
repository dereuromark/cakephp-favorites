<?php
declare(strict_types=1);

namespace Favorites\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Response;
use Cake\Log\Log;
use Closure;
use Throwable;

/**
 * Admin namespace for the Favorites plugin.
 *
 * The default policy is **deny**: the host application MUST set
 * `Favorites.adminAccess` to a `Closure` that receives the current request
 * and returns literal `true` to grant access. Anything else (unset,
 * non-Closure, returns false, returns a truthy non-bool, or throws) yields
 * a 403. (Issue #4 — patterned after Queue.adminAccess.)
 *
 * ```php
 * Configure::write('Favorites.adminAccess', function (\Cake\Http\ServerRequest $request): bool {
 *     $identity = $request->getAttribute('identity');
 *     return $identity !== null && in_array('admin', (array)$identity->roles, true);
 * });
 * ```
 *
 * @property \Favorites\Model\Table\FavoritesTable $Favorites
 * @method \Favorites\Model\Entity\Favorite[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class FavoritesController extends AppController {

	/**
	 * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event
	 *
	 * @throws \Cake\Http\Exception\ForbiddenException When access is denied or unconfigured.
	 *
	 * @return void
	 */
	public function beforeFilter(EventInterface $event): void {
		parent::beforeFilter($event);

		// Coexist with cakephp/authorization: the gate IS the authorization decision
		// for these controllers, so silence the policy check.
		if ($this->components()->has('Authorization') && method_exists($this->components()->get('Authorization'), 'skipAuthorization')) {
			$this->components()->get('Authorization')->skipAuthorization();
		}

		$gate = Configure::read('Favorites.adminAccess');
		if (!($gate instanceof Closure)) {
			throw new ForbiddenException(__d(
				'favorites',
				'Favorites admin backend is not configured. Set Favorites.adminAccess to a Closure that returns true for permitted callers.',
			));
		}

		try {
			$allowed = $gate($this->request) === true;
		} catch (ForbiddenException $e) {
			throw $e;
		} catch (Throwable $e) {
			Log::warning(sprintf('Favorites.adminAccess threw %s: %s', $e::class, $e->getMessage()));

			throw new ForbiddenException(__d('favorites', 'Favorites admin access denied.'));
		}

		if (!$allowed) {
			throw new ForbiddenException(__d('favorites', 'Favorites admin access denied.'));
		}
	}

	/**
	 * @return \Cake\Http\Response|null|void Renders view
	 */
	public function index() {
		if ($this->request->is(['post', 'put'])) {
			$model = $this->request->getQuery('model');
			// Whitelist against `Favorites.models` — without this, `reset()` happily issues
			// `DELETE FROM favorites_favorites WHERE model = ''` for unmapped values, and the
			// query parameter would pollute the i18n cache when concatenated into __() (Issue #3).
			$allowed = array_keys((array)Configure::read('Favorites.models'));
			if (!is_string($model) || !in_array($model, $allowed, true)) {
				throw new BadRequestException('Invalid model');
			}
			$count = $this->Favorites->reset($model);
			$this->Flash->success(__('The favorites have been reset for `{0}`, deleted: {1}.', $model, $count));

			return $this->redirect(['action' => 'index']);
		}

		$models = $this->Favorites->find()
			->select(['model', 'count' => $this->Favorites->find()->func()->count('*')])
			->where(['model IS NOT' => null])
			->groupBy('model')
			->find('list', ...['keyField' => 'model', 'valueField' => 'count'])
			->toArray();

		$this->set(compact('models'));
	}

	/**
	 * @return \Cake\Http\Response|null|void Renders view
	 */
	public function listing() {
		$query = $this->Favorites->find()
			->contain(['Users']);
		$favorites = $this->paginate($query);

		$this->set(compact('favorites'));
	}

	/**
	 * @param string|null $id Favorite id.
	 *
	 * @return \Cake\Http\Response|null Redirects to index.
	 */
	public function delete(?string $id = null): ?Response {
		$this->request->allowMethod(['post', 'delete']);
		$favorite = $this->Favorites->get($id);
		if ($this->Favorites->delete($favorite)) {
			$this->Flash->success(__('The favorite has been deleted.'));
		} else {
			$this->Flash->error(__('The favorite could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
