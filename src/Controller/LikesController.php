<?php

namespace Favorites\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use TinyAuth\Controller\Component\AuthComponent;
use TinyAuth\Controller\Component\AuthUserComponent;

/**
 * @property \Favorites\Model\Table\FavoritesTable $Favorites
 * @property \TinyAuth\Controller\Component\AuthUserComponent $AuthUser
 * @property \TinyAuth\Controller\Component\AuthComponent $Auth
 */
class LikesController extends AppController {

	use AuthTrait;

	protected ?string $defaultTable = 'Favorites.Favorites';

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		if (class_exists(AuthUserComponent::class)) {
			$this->loadComponent('TinyAuth.AuthUser');
		} elseif (class_exists(AuthComponent::class)) {
			$this->loadComponent('TinyAuth.Auth');
		}
	}

	/**
	 * @param string|null $alias
	 * @param int|null $id
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function like(?string $alias = null, ?int $id = null): ?Response {
		$this->request->allowMethod(['post', 'put', 'patch']);

		$model = Configure::read('Favorites.models.' . $alias);
		if (!$model) {
			throw new NotFoundException('Invalid alias');
		}
		$table = $this->fetchTable($model);
		$entity = $table->get($id);

		$uid = $this->userId();
		if (!$uid) {
			throw new MethodNotAllowedException('Must be logged in to add like');
		}

		$result = $this->Favorites->add($model, $entity->get('id'), $uid, 1);
		if ($result->isNew()) {
			$this->Flash->error(__d('favorites', 'Could not save like, please try again.'));
		}

		return $this->redirect($this->referer(['action' => 'index']));
	}

	/**
	 * @param string|null $alias
	 * @param int|null $id
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function dislike(?string $alias = null, ?int $id = null): ?Response {
		$this->request->allowMethod(['post', 'put', 'patch']);

		$model = Configure::read('Favorites.models.' . $alias);
		if (!$model) {
			throw new NotFoundException('Invalid alias');
		}
		$table = $this->fetchTable($model);
		$entity = $table->get($id);

		$uid = $this->userId();
		if (!$uid) {
			throw new MethodNotAllowedException('Must be logged in to add dislike');
		}

		$result = $this->Favorites->add($model, $entity->get('id'), $uid, -1);
		// `isNew()` is true for unsaved entities — that IS the failure case (Issue #1).
		// Flash an error only when persistence actually didn't happen.
		if ($result->isNew()) {
			$this->Flash->error(__d('favorites', 'Could not save dislike, please try again.'));
		}

		return $this->redirect($this->referer(['action' => 'index']));
	}

	/**
	 * @param string|null $alias
	 * @param int|null $id
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function remove(?string $alias = null, ?int $id = null): ?Response {
		$this->request->allowMethod(['post', 'delete']);

		$model = Configure::read('Favorites.models.' . $alias);
		if (!$model) {
			throw new NotFoundException('Invalid alias');
		}
		$table = $this->fetchTable($model);
		$entity = $table->get($id);

		$uid = $this->userId();
		if (!$uid) {
			throw new MethodNotAllowedException('Must be logged in to remove like/dislike');
		}

		$this->Favorites->remove($model, $entity->get('id'), $uid);

		return $this->redirect($this->referer(['action' => 'index']));
	}

	/**
	 * @param int|null $id
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function delete(?int $id = null): ?Response {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getData('id') ?: $id;
		$favorite = $this->Favorites->get($id);

		$userId = $this->userId();
		if ($favorite->user_id !== $userId) {
			throw new NotFoundException(__d('favorites', 'You are not authorized to remove this favorite.'));
		}

		$this->Favorites->delete($favorite);

		return $this->redirect($this->referer(['action' => 'index']));
	}

}
