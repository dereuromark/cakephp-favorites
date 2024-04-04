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
class LikesHelper extends Helper {

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
		'html' => null,
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
			static::HTML_TYPE_UTF8 => [
				1 => '👍',
				-1 => '👎',
				0 => '❌',
			],
			static::HTML_TYPE_FA6 => [
				1 => '<span class="fa fa-arrow-up" title="Yep"></span>',
				-1 => '<span class="fa fa-arrow-down" title="Nope"></span>',
				0 => '<span class="fa fa-remove" title="Remove"></span>',
			],
			default => $iconType,
		};
		$config['html'] = $html;

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
			$icon .= ' <details class="like-buttons"><summary>' . __d('favorites', 'Reset rating') . '</summary>' . $this->Form->postLink($resetIcon, $url, $options) . '</details>';

			return $icon;
		}

		$likeIcon = $this->icon($alias, $id, 1);
		$disLikeIcon = $this->icon($alias, $id, -1);
		$likeUrl = $this->url('like', $alias, $id);
		$dislikeUrl = $this->url('dislike', $alias, $id);
		$likeData = $this->data('like', $alias, $id);
		$dislikeData = $this->data('dislike', $alias, $id);

		$icons = [];
		$icons[] = $this->Form->postLink($likeIcon, $likeUrl, ['escapeTitle' => false, 'block' => true, 'data' => $likeData]);
		$icons[] = $this->Form->postLink($disLikeIcon, $dislikeUrl, ['escapeTitle' => false, 'block' => true, 'data' => $dislikeData]);

		return '<details class="like-buttons"><summary>' . __d('favorites', 'Rate me') . '</summary>' . implode(' ', $icons) . '</details>';
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
			Config::STRATEGY_CONTROLLER => ['plugin' => 'Favorites', 'controller' => 'Stars', 'action' => $action, $alias, $id],
			default => throw new BadMethodCallException('Not implemented: ' . $strategy),
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
			Config::STRATEGY_ACTION => ['favorite' => 'like', 'action' => $action, 'alias' => $alias, 'id' => $id],
			Config::STRATEGY_CONTROLLER => [],
			default => throw new BadMethodCallException('Not implemented: ' . $strategy),
		};
	}

	/**
	 * @param string $action
	 *
	 * @return string
	 */
	protected function action(string $action): string {
		return match ($action) {
			'like' => 'like',
			'dislike' => 'dislike',
			'remove' => 'remove',
			default => throw new BadMethodCallException('Not implemented: ' . $action)
		};
	}

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
	 * @param int|null $value
	 *
	 * @return string
	 */
	public function icon(string $alias, int|string $id, ?int $value = null) {
		if ($value === null) {
			$value = $this->value($alias, $id);
		}

		/** @var array<int, string> $html */
		$html = $this->getConfig('html');
		$iconHtml = '';
		if ($value !== null) {
			$iconHtml = $html[$value] ?? '';
		}

		return $iconHtml;
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
