<?php
declare(strict_types=1);

namespace Favorites\Controller\Admin;

use App\Controller\AppController;

/**
 * @property \Favorites\Model\Table\FavoritesTable $Favorites
 * @method \Favorites\Model\Entity\Favorite[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class FavoritesController extends AppController {

	/**
	 * @return \Cake\Http\Response|null|void Renders view
	 */
	public function index() {
		if ($this->request->is(['post', 'put'])) {
			$model = $this->request->getQuery('model');
			$count = $this->Favorites->reset($model);
			$this->Flash->success(__('The favorites have been reset for `' . $model . '`, deleted: ' . $count . '.'));

			return $this->redirect(['action' => 'index']);
		}

		$models = $this->Favorites->find()
			->select(['model', 'count' => $this->Favorites->find()->func()->count('*')])
			->where(['model IS NOT' => null])
			->groupBy('model')
			->find('list', ['keyField' => 'model', 'valueField' => 'count'])
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
	public function delete($id = null) {
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
