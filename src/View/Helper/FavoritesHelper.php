<?php

namespace Favorites\View\Helper;

use BadMethodCallException;
use Cake\Core\Configure;
use Cake\Datasource\ModelAwareTrait;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\View\Helper;
use Favorites\Utility\Config;
use RuntimeException;

/**
 * @property \Cake\View\Helper\UrlHelper $Url
 * @property \Cake\View\Helper\FormHelper $Form
 */
class FavoritesHelper extends Helper {

	use ModelAwareTrait;
	use AuthTrait;

	/**
	 * @var array<int, string>
	 */
	public const ICONS_GITHUB = [
		1 => '👍',
		-1 => '👎',
		2 => '😄',
		3 => '😕',
		4 => '❤️',
		5 => '🎉',
		6 => '🚀',
		7 => '👀',
	];

	/**
	 * @var string
	 */
	public const ICON_RESET = '❌';

	/**
	 * @var array
	 */
	protected array $helpers = [
		'Url',
		'Form',
	];

	/**
	 * @var array<string, mixed>
	 */
	protected array $_defaultConfig = [
		'strategy' => Config::STRATEGY_CONTROLLER,
		'icons' => [],
		'resetIcon' => null,
		'html' => '<span class="reaction%s">%s</span>',
	];

	/**
	 * @param array<string, mixed> $config
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		$config += (array)Configure::read('Favorites');
		$this->setConfig($config);
	}

	/**
	 * @param string $alias
	 * @param string|int $id
	 * @param int|null $value
	 *
	 * @return string
	 */
	public function widget(string $alias, int|string $id, ?int $value = null): string {
		if ($value === null) {
			$value = $this->value($alias, $id);
		}

		$icon = $this->icon($alias, $id, $value);
		if ($icon) {
			$url = $this->url('remove', $alias, $id);
			$data = $this->data('remove', $alias, $id);
			$resetIcon = $this->icon($alias, $id, 0);
			$options = ['escapeTitle' => false, 'block' => true, 'data' => $data];
			$icon .= ' <details class="like-buttons"><summary>' . __d('favorites', 'Remove reaction') . '</summary>' . $this->Form->postLink($resetIcon, $url, $options) . '</details>';

			return $icon;
		}

		$iconList = [];

		/** @var array<int, string> $icons */
		$icons = $this->getConfig('icons') ?: [];
		if (!$icons) {
			throw new RuntimeException('Icons are not defined yet');
		}

		foreach ($icons as $value => $icon) {
			$html = $this->icon($alias, $id, $value);
			$url = $this->url('add', $alias, $id);
			$data = $this->data('add', $alias, $id, $value);
			$iconList[] = $this->Form->postLink($html, $url, ['escapeTitle' => false, 'block' => true, 'data' => $data]);
		}

		return '<details class="like-buttons"><summary>' . __d('favorites', 'Add reaction') . '</summary>' . implode(' ', $iconList) . '</details>';
	}

	/**
	 * @param string $alias
	 * @param string|int $id
	 * @param int|null $value
	 *
	 * @return string
	 */
	public function icon(string $alias, int|string $id, ?int $value = null) {
		if ($value === null) {
			$value = $this->value($alias, $id);
		}

		/** @var string $html */
		$html = $this->getConfig('html');
		$iconHtml = '';
		if ($value === 0) {
			$icon = $this->getConfig('resetIcon') ?? static::ICON_RESET;
			$iconHtml = sprintf($html, '', $icon);
		} elseif ($value !== null) {
			$icon = $this->iconSymbol($value);
			$iconHtml = sprintf($html, '', $icon);
		}

		return $iconHtml;
	}

	/**
	 * @param string $action
	 * @param string $alias
	 * @param string|int $id
	 * @param int|null $value
	 *
	 * @return array
	 */
	protected function data(string $action, string $alias, int|string $id, ?int $value = null): array {
		$strategy = Config::strategy($this->getConfig('strategy'));

		return match ($strategy) {
			Config::STRATEGY_ACTION => ['favorite' => 'favorite', 'action' => $action, 'alias' => $alias, 'id' => $id, 'value' => $value],
			Config::STRATEGY_CONTROLLER => ['value' => $value],
			default => throw new BadMethodCallException('Not implemented: ' . $strategy),
		};
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
			Config::STRATEGY_ACTION => $this->_View->getRequest()->getUri()->getPath(),
			Config::STRATEGY_CONTROLLER => ['plugin' => 'Favorites', 'controller' => 'Favorites', 'action' => $action, $alias, $id],
			default => throw new BadMethodCallException('Not implemented: ' . $strategy),
		};
	}

	/**
	 * @param string $alias
	 * @param string|int $id
	 *
	 * @return int|null
	 */
	public function value(string $alias, int|string $id): ?int {
		$uid = $this->userId();
		if (!$uid) {
			throw new MethodNotAllowedException('Must be logged in');
		}

		$class = $this->getConfig('favoriteClass') ?: 'Favorites.Favorites';
		$table = $this->fetchModel($class);

		$entity = $table->find()
			->select(['id', 'value'])
			->where([
				'model' => $alias,
				'foreign_key' => $id,
				'user_id' => $uid,
			])->first();

		return $entity ? $entity->value : null;
	}

	/**
	 * @param int $value
	 *
	 * @return string
	 */
	protected function iconSymbol(int $value): string {
		$icons = $this->getConfig('icons');
		if (!isset($icons[$value])) {
			throw new BadMethodCallException('No such icon value: ' . $value);
		}

		return $icons[$value];
	}

	/**
	 * @param string $alias
	 * @param string|int $id
	 *
	 * @throws \Cake\Http\Exception\NotFoundException
	 *
	 * @return array|string
	 */
	public function urlAdd(string $alias, int|string $id): string|array {
		return $this->url('add', $alias, $id);
	}

	/**
	 * @param string $alias
	 * @param string|int $id
	 *
	 * @throws \Cake\Http\Exception\NotFoundException
	 *
	 * @return array|string
	 */
	public function urlRemove(string $alias, int|string $id): string|array {
		return $this->url('remove', $alias, $id);
	}

}
