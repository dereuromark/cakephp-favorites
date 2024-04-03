<?php

namespace Favorites\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Behavior;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Favorites\Model\Table\FavoritesTable;

class LikeableBehavior extends Behavior {

	/**
	 * Default settings
	 *
	 * @var array<string, mixed>
	 */
	protected array $_defaultConfig = [
		'modelClass' => null, // Auto-detect
		'favoriteClass' => 'Favorites.Favorites',
		'userModelClass' => 'Users',
		'userModelConfig' => null,
		'countFavorites' => false,
		'implementedFinders' => [
			'liked' => 'findLiked',
			'LikedBy' => 'findLikedBy',
		],
	];

	/**
	 * Constructor
	 *
	 * Merges config with the default and store in the config property
	 *
	 * @param \Cake\ORM\Table $table The table this behavior is attached to.
	 * @param array<string, mixed> $config The config for this behavior.
	 */
	public function __construct(Table $table, array $config = []) {
		$config += (array)Configure::read('Favorites');

		parent::__construct($table, $config);
	}

	/**
	 * Setup
	 *
	 * @param array $config default config
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		if (!$this->getConfig('model')) {
			$this->setConfig('model', $this->_table->getAlias());
		}
		if (!$this->getConfig('modelClass')) {
			$this->setConfig('modelClass', $this->_table->getRegistryAlias());
		}
		if (!$this->getConfig('userModel')) {
			[, $alias] = pluginSplit($this->getConfig('userModelClass'));
			$this->setConfig('userModel', $alias);
		}

		$this->_table->hasMany('Favorites', [
			'className' => $this->getConfig('favoriteClass'),
			'foreignKey' => 'foreign_key',
			'order' => 'Favorites.created DESC',
			'conditions' => ['Favorites.model' => "{$this->_table->getAlias()}"],
			'dependent' => true,
		]);

		$this->favoritesTable()->belongsTo($this->getConfig('model'), [
			'className' => $this->getConfig('modelClass'),
			'foreignKey' => 'foreign_key',
		]);

		if (!empty($config['userModelConfig']) && is_array($config['userModelConfig'])) {
			$this->favoritesTable()->belongsTo($config['userModel'], $config['userModelConfig']);
		} else {
			$userConfig = [
				'className' => $this->getConfig('userModelClass'),
				'foreignKey' => 'user_id',
			];
			$this->favoritesTable()->belongsTo($this->getConfig('userModel'), $userConfig);
		}
	}

	/**
	 * Handle adding favorites
	 *
	 * @param array $options extra information and favorite statistics
	 *
	 * @throws \Cake\Http\Exception\MethodNotAllowedException
	 *
	 * @return int|null
	 */
	public function addLike(array $options = []) {
		$options += ['value' => 1, 'model' => $this->getConfig('model'), 'modelId' => null, 'userId' => null];

		$favorite = $this->favoritesTable()->add($options['model'], $options['modelId'], $options['userId'], $options['value']);
		if (!$favorite->isNew()) {
			return $favorite->id;
		}

		return null;
	}

	/**
	 * Handle adding favorites
	 *
	 * @param array $options extra information and favorite statistics
	 *
	 * @throws \Cake\Http\Exception\MethodNotAllowedException
	 *
	 * @return int|null
	 */
	public function addDislike(array $options = []) {
		$options += ['value' => -1, 'model' => $this->getConfig('model'), 'modelId' => null, 'userId' => null];

		$favorite = $this->favoritesTable()->add($options['model'], $options['modelId'], $options['userId'], $options['value']);
		if (!$favorite->isNew()) {
			return $favorite->id;
		}

		return null;
	}

	/**
	 * Create the finder favorites
	 *
	 * @param \Cake\ORM\Query\SelectQuery $query
	 *
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function findLiked(SelectQuery $query): SelectQuery {
		$subQuery = $this->buildLikedQuerySnippet(1);
		$modelAlias = $this->_table->getAlias();

		return $query->where([$modelAlias . '.id IN' => $subQuery]);
	}

	/**
	 * Create the finder favorites
	 *
	 * @param \Cake\ORM\Query\SelectQuery $query
	 *
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function findDisliked(SelectQuery $query): SelectQuery {
		$subQuery = $this->buildLikedQuerySnippet(-1);
		$modelAlias = $this->_table->getAlias();

		return $query->where([$modelAlias . '.id IN' => $subQuery]);
	}

	/**
	 * @return \Favorites\Model\Table\FavoritesTable
	 */
	protected function favoritesTable(): FavoritesTable {
		/** @var \Favorites\Model\Table\FavoritesTable */
		return $this->_table->Favorites->getTarget();
	}

	/**
	 * @param int $value
	 *
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	protected function buildLikedQuerySnippet(int $value): SelectQuery {
		$model = $this->getConfig('model');
		$conditions = [
			$this->favoritesTable()->getAlias() . '.model' => $model,
			$this->favoritesTable()->getAlias() . '.value' => $value,
		];

		return $this->favoritesTable()->find()
			->select($this->favoritesTable()->getAlias() . '.foreign_key')
			->where($conditions);
	}

}
