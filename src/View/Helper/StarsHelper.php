<?php

namespace Favorites\View\Helper;

use BadMethodCallException;
use Cake\Core\Configure;
use Cake\Datasource\ModelAwareTrait;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\Exception\NotFoundException;
use Cake\View\Helper;
use Favorites\Utility\Config;

/**
 * @property \Cake\View\Helper\UrlHelper $Url
 * @property \Cake\View\Helper\FormHelper $Form
 */
class StarsHelper extends Helper {

	use ModelAwareTrait;
	use AuthTrait;

	/**
	 * @var int
	 */
	public const HTML_TYPE_UTF8 = 0;

	/**
	 * @var int
	 */
	public const HTML_TYPE_FA6 = 1;

	/**
	 * @var array<string, mixed>
	 */
	protected array $_defaultConfig = [
		'strategy' => Config::STRATEGY_CONTROLLER,
		'html' => null,
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
	 * @param array<string, mixed> $config
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		$config += (array)Configure::read('Favorites');

		/** @var string|int|null $iconType */
		$iconType = $config['html'] ?? static::HTML_TYPE_UTF8;
		$html = match ($iconType) {
			static::HTML_TYPE_UTF8 => '<span class="star%s"%s>★</span>',
			static::HTML_TYPE_FA6 => '<span class="fa-solid fa-star%s"%s></span>',
			default => $iconType,
		};
		$config['html'] = $html;

		$this->setConfig($config);
	}

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

		$icon = $this->icon($alias, $id, $value);
		$action = $value ? 'unstar' : 'star';
		$url = $this->url($action, $alias, $id);
		$data = $this->data($action, $alias, $id);

		return $this->Form->postLink($icon, $url, ['escapeTitle' => false, 'block' => true, 'data' => $data]);
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

		$class = $this->getConfig('favoriteClass') ?: 'Favorites.Favorites';
		$table = $this->fetchModel($class);

		$entity = $table->find()
			->select(['id'])
			->where([
			'model' => $alias,
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
		$model = $this->getConfig('models.' . $alias);
		if (!$model) {
			throw new NotFoundException('Invalid alias');
		}

		return $model;
	}

	/**
	 * @param string $action
	 * @param string $alias
	 * @param string|int $id
	 *
	 * @return array|string
	 */
	protected function url(string $action, string $alias, int|string $id): string|array {
		$strategy = Config::strategy($this->getConfig('strategy'));

		return match ($strategy) {
			Config::STRATEGY_ACTION => $this->_View->getRequest()->getUri()->getPath(), //['plugin' => 'Favorites', 'controller' => 'Stars', 'action' => $action, $alias, $id],
			Config::STRATEGY_CONTROLLER => ['plugin' => 'Favorites', 'controller' => 'Stars', 'action' => $action, $alias, $id],
			default => throw new BadMethodCallException('Not implemented'),
		};
	}

	/**
	 * @param string $action
	 * @param string $alias
	 * @param string|int $id
	 *
	 * @return array
	 */
	protected function data(string $action, string $alias, int|string $id): array {
		$strategy = Config::strategy($this->getConfig('strategy'));

		return match ($strategy) {
			Config::STRATEGY_ACTION => ['favorite' => 'star', 'action' => $action, 'alias' => $alias, 'id' => $id],
			Config::STRATEGY_CONTROLLER => [],
			default => throw new BadMethodCallException('Not implemented'),
		};
	}

}
