<?php

namespace Favorites\Utility;

use Cake\Core\Configure;

class Config {

	/**
	 * @var string
	 */
	public const TYPE_STAR = 'star';

	/**
	 * @var string
	 */
	public const TYPE_LIKE = 'like';

	/**
	 * @var string
	 */
	public const TYPE_FAVORITE = 'favorite';

	/**
	 * @param string $type
	 *
	 * @return array<string>
	 */
	public static function controllerModels(string $type): array {
		$models = Configure::read('Favorites.controllerModels');
		if (isset($models[$type])) {
			return array_merge($models[$type], $models);
		}

		return $models;
	}

}
