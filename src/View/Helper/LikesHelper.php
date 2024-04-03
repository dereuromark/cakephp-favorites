<?php

namespace Favorites\View\Helper;

use Cake\Core\Configure;
use Cake\Datasource\ModelAwareTrait;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\Exception\NotFoundException;
use Cake\View\Helper;
use InvalidArgumentException;

/**
 * @property \Cake\View\Helper\UrlHelper $Url
 * @property \Cake\View\Helper\FormHelper $Form
 */
class LikesHelper extends Helper {

	use ModelAwareTrait;
	use AuthTrait;

	/**
	 * @var array<string, mixed>
	 */
	protected array $_defaultConfig = [
	];

	/**
	 * @var array
	 */
	protected array $helpers = [
		'Url',
		'Form',
	];

	/**
	 * @param string $alias
	 * @param string|int $id
	 *
	 * @throws \Cake\Http\Exception\NotFoundException
	 *
	 * @return string
	 */
	public function urlLike(string $alias, int|string $id): string {
		return $this->Url->build(['plugin' => 'Favorites', 'controller' => 'Likes', 'action' => 'like', $alias, $id]);
	}

	/**
	 * @param string $alias
	 * @param string|int $id
	 *
	 * @throws \Cake\Http\Exception\NotFoundException
	 *
	 * @return string
	 */
	public function urlDislike(string $alias, int|string $id): string {
		return $this->Url->build(['plugin' => 'Favorites', 'controller' => 'Likes', 'action' => 'dislike', $alias, $id]);
	}

	/**
	 * @param string $alias
	 * @param string|int $id
	 *
	 * @return bool
	 */
	public function value(string $alias, int|string $id): bool {
		$uid = $this->userId();
		if (!$uid) {
			throw new MethodNotAllowedException('Must be logged in');
		}

		$model = $this->model($alias);
		if (!$model) {
			throw new InvalidArgumentException('Model not found for alias ' . $alias);
		}
		$class = Configure::read('Favorites.favoriteClass') ?: 'Favorites.Favorites';
		$table = $this->fetchModel($class);

		$entity = $table->find()
			->select(['id', 'value'])
			->where([
			'model' => $model,
			'foreign_key' => $id,
			'user_id' => $uid,
		])->first();

		return $entity->value;
	}

	/**
	 * @param string $alias
	 *
	 * @return string
	 */
	protected function model(string $alias): string {
		$model = Configure::read('Favorites.models.' . $alias);
		if (!$model) {
			throw new NotFoundException('Invalid alias');
		}

		return $model;
	}

}
