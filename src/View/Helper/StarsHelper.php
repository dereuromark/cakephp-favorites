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
class StarsHelper extends Helper {

	use ModelAwareTrait;
	use AuthTrait;

	/**
	 * @var array<string, mixed>
	 */
	protected array $_defaultConfig = [
		'html' => '<span class="fa-solid fa-star%s"%s></span>',
		'colorMap' => [],
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
	 * @param bool|null $value
	 *
	 * @return string
	 */
	public function icon(string $alias, int|string $id, ?bool $value = null) {
		if ($value === null) {
			$value = $this->value($alias, $id);
		}

		$html = $this->getConfig('html');
		$colorMap = $this->getConfig('colorMap');
		if (!$colorMap && $colorMap !== false) {
			$colorMap = [
				0 => '#aaa',
				1 => '#ffa500',
			];
		}

		$value = (int)$value;
		$title = $value ? __d('favorites', 'Starred by you. Click to unstar.') : __d('favorites', 'Click to star.');

		$attributes = [];
		$attributes[] = 'title="' . $title . '"';
		if ($colorMap && !empty($colorMap[$value])) {
			$attributes[] = 'style="color: ' . $colorMap[$value] . '"';
		}

		$class = $value ? ' starred' : '';
		$icon = sprintf($html, $class, ' ' . implode(' ', $attributes));

		return $icon;
	}

	/**
	 * @param string $alias
	 * @param string|int $id
	 * @param bool|null $value
	 *
	 * @return string
	 */
	public function linkIcon(string $alias, int|string $id, ?bool $value = null): string {
		if ($value === null) {
			$value = $this->value($alias, $id);
		}

		$action = $value ? 'unstar' : 'star';
		$url = ['plugin' => 'Favorites', 'controller' => 'Stars', 'action' => $action, $alias, $id];

		$icon = $this->icon($alias, $id, $value);

		return $this->Form->postLink($icon, $url, ['escapeTitle' => false, 'block' => true]);
	}

	/**
	 * @param string $alias
	 * @param string|int $id
	 *
	 * @throws \Cake\Http\Exception\NotFoundException
	 *
	 * @return string
	 */
	public function urlStar(string $alias, int|string $id): string {
		return $this->Url->build(['plugin' => 'Favorites', 'controller' => 'Stars', 'action' => 'star', $alias, $id]);
	}

	/**
	 * @param string $alias
	 * @param string|int $id
	 *
	 * @throws \Cake\Http\Exception\NotFoundException
	 *
	 * @return string
	 */
	public function urlUnstar(string $alias, int|string $id): string {
		return $this->Url->build(['plugin' => 'Favorites', 'controller' => 'Stars', 'action' => 'unstar', $alias, $id]);
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
			->select(['id'])
			->where([
			'model' => $model,
			'foreign_key' => $id,
			'user_id' => $uid,
		])->first();

		return (bool)$entity;
	}

	/**
	 * @param string $alias
	 *
	 * @return string
	 */
	protected function model(string $alias): string {
		$model = Configure::read('Favorites.controllerModels.' . $alias);
		if (!$model) {
			throw new NotFoundException('Invalid alias');
		}

		return $model;
	}

}
