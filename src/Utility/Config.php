<?php

namespace Favorites\Utility;

use Cake\Core\Configure;
use InvalidArgumentException;

class Config {

	/**
	 * @var string
	 */
	public const STRATEGY_CONTROLLER = 'controller';

	/**
	 * @var string
	 */
	public const STRATEGY_ACTION = 'action';

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
	 * @param string $strategy
	 *
	 * @return string
	 */
	public static function strategy(string $strategy): string {
		if (!in_array($strategy, [static::STRATEGY_ACTION, static::STRATEGY_CONTROLLER], true)) {
			throw new InvalidArgumentException('Invalid strategy ' . $strategy);
		}

		return $strategy;
	}

	/**
	 * @param string $model
	 * @param string $type
	 *
	 * @return string|null
	 */
	public static function alias(string $model, string $type): ?string {
		$models = static::models($type);

		/** @var string[] $keys */
		$keys = array_keys($models, $model);

		return $keys ? array_shift($keys) : null;
	}

	/**
	 * @param string $type
	 *
	 * @return array<string>
	 */
	public static function models(string $type): array {
		$models = (array)Configure::read('Favorites.models');
		if (isset($models[$type])) {
			return array_merge($models[$type], $models);
		}

		return $models;
	}

}
