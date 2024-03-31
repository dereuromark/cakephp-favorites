<?php

namespace Favorites\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use TinyAuth\Controller\Component\AuthComponent;
use TinyAuth\Controller\Component\AuthUserComponent;

/**
 * @property \Favorites\Model\Table\FavoritesTable $Favorites
 * @property \TinyAuth\Controller\Component\AuthUserComponent $AuthUser
 * @property \TinyAuth\Controller\Component\AuthComponent $Auth
 */
class FavoritesController extends AppController {

	protected ?string $modelClass = 'Favorites.Favorites';

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
	public function add($alias = null, $id = null) {
		$this->request->allowMethod(['post', 'put', 'patch']);
		$data = $this->request->getData();

		$model = Configure::read('Favorites.controllerModels.' . $alias);
		if (!$model) {
			throw new NotFoundException('Invalid alias');
		}
		$table = $this->fetchTable($model);
		$entity = $table->get($id);

		$data['model'] = $model;
		$data['foreign_key'] = $entity->get('id');
		$data['user_id'] = $this->userId();

		$result = $this->Favorites->add($data);
		if (!$result->isNew()) {
			$this->Flash->error(__d('favorites', 'Could not save favorite, please try again.'));
		}

		return $this->redirect($this->referer(['action' => 'index']));
	}

	/**
	 * @param string|null $alias
	 * @param int|null $id
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function remove($alias = null, $id = null) {
		$this->request->allowMethod(['post', 'delete']);
		$data = $this->request->getData();

		$model = Configure::read('Favorites.controllerModels.' . $alias);
		if (!$model) {
			throw new NotFoundException('Invalid alias');
		}
		$table = $this->fetchTable($model);
		$entity = $table->get($id);

		$data['model'] = $model;
		$data['foreign_key'] = $entity->get('id');
		$data['user_id'] = $this->userId();

		$this->Favorites->remove($data);

		return $this->redirect($this->referer(['action' => 'index']));
	}

	/**
	 * @return int|null
	 */
	protected function userId() {
		$userIdField = Configure::read('Favorites.userIdField') ?: 'id';
		if ($this->components()->has('AuthUser')) {
			return $this->AuthUser->user($userIdField);
		}
		if ($this->components()->has('Auth')) {
			return $this->Auth->user('id');
		}

		return $this->getRequest()->getSession()->read('Auth.User.' . $userIdField);
	}

	/**
	 * @param int|null $id
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function delete($id = null) {
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
