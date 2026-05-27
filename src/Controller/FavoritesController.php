<?php

namespace Favorites\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use InvalidArgumentException;

/**
 * @property \Favorites\Model\Table\FavoritesTable $Favorites
 */
class FavoritesController extends AppController {

	use AuthTrait;

	protected ?string $defaultTable = 'Favorites.Favorites';

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		if (class_exists('TinyAuth\\Controller\\Component\\AuthUserComponent')) {
			$this->loadComponent('TinyAuth.AuthUser');
		} elseif (class_exists('TinyAuth\\Controller\\Component\\AuthComponent')) {
			$this->loadComponent('TinyAuth.Auth');
		}
	}

	/**
	 * @param string|null $alias
	 * @param mixed $id
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function add(?string $alias = null, $id = null): ?Response {
		$this->request->allowMethod(['post', 'put', 'patch']);

		$model = Configure::read('Favorites.models.' . $alias);
		if (!$model) {
			throw new NotFoundException('Invalid alias');
		}
		$table = $this->fetchTable($model);
		$entity = $table->get($id);

		$uid = $this->userId();
		if (!$uid) {
			throw new MethodNotAllowedException('Must be logged in to remove favorite');
		}
		/** @var string|float|int|bool|null $value */
		$value = $this->request->getData('value');
		if ($value !== null) {
			$value = (int)$value;
		}
		if (!$value) {
			throw new InvalidArgumentException('Missing value for Favorite ' . $alias . ':' . $id);
		}

		$result = $this->Favorites->add($model, $entity->get('id'), $uid, $value);
		if ($result->hasErrors()) {
			$this->Flash->error(__d('favorites', 'Could not save favorite, please try again.'));
		}

		return $this->redirect($this->referer(['action' => 'index']));
	}

	/**
	 * @param string|null $alias
	 * @param mixed $id
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function remove(?string $alias = null, $id = null): ?Response {
		$this->request->allowMethod(['post', 'delete']);

		$model = Configure::read('Favorites.models.' . $alias);
		if (!$model) {
			throw new NotFoundException('Invalid alias');
		}
		$table = $this->fetchTable($model);
		$entity = $table->get($id);

		$uid = $this->userId();
		if (!$uid) {
			throw new MethodNotAllowedException('Must be logged in to remove favorite');
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
