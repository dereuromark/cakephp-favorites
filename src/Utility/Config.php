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
		if (is_array($models[$type] ?? null)) {
			return $models[$type] + array_filter($models, 'is_string');
		}

		return array_filter($models, 'is_string');
	}

	/**
	 * @param string $alias
	 *
	 * @return list<string>
	 */
	public static function storageModels(string $alias): array {
		$legacyModels = (array)Configure::read('Favorites.legacyModels');
		$result = [$alias];
		foreach ((array)($legacyModels[$alias] ?? []) as $legacy) {
			if (!is_string($legacy) || $legacy === '') {
				continue;
			}
			foreach ($legacyModels as $otherAlias => $names) {
				if ($otherAlias !== $alias && in_array($legacy, (array)$names, true)) {
					throw new InvalidArgumentException('Legacy model name is assigned to several aliases: ' . $legacy);
				}
			}
			foreach ([static::TYPE_STAR, static::TYPE_LIKE, static::TYPE_FAVORITE] as $type) {
				$models = static::models($type);
				if ($legacy !== $alias && isset($models[$legacy])) {
					throw new InvalidArgumentException('Legacy model name is an active public alias: ' . $legacy);
				}
			}
			$result[] = $legacy;
		}

		return array_values(array_unique($result));
	}

	/**
	 * @param mixed $value
     *
	 * @return int|null
	 */
	public static function userId(mixed $value): ?int {
		if (!is_int($value) && (!is_string($value) || !ctype_digit($value))) {
			return null;
		}
		$id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

		return $id === false ? null : $id;
	}

}
