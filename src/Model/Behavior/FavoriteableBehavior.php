<?php

namespace Favorites\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Favorites\Model\Table\FavoritesTable;

class FavoriteableBehavior extends Behavior {

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
		'implementedFinders' => [
			'favorites' => 'findFavorites',
			'favoritesBy' => 'findFavoritesBy',
		],
		'multiple' => false, //TODO
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
			'conditions' => ['Favorites.model' => $this->getConfig('model')],
			'dependent' => true,
		]);

		if (!empty($config['userId'])) {
			$this->_table->hasOne('Favorite', [
				'className' => $this->getConfig('favoriteClass'),
				'foreignKey' => 'foreign_key',
				'conditions' => ['Favorite.model' => $this->getConfig('model'), 'Favorite.user_id' => $config['userId']],
				'dependent' => true,
			]);
		}

		$this->favoritesTable()->belongsTo($this->getConfig('modelClass'), [
			'className' => $this->getConfig('modelClass'),
			'foreignKey' => 'foreign_key',
		]);

		if ($this->getConfig('userModelConfig') && !$this->favoritesTable()->hasAssociation($this->getConfig('userModel'))) {
			$this->favoritesTable()->belongsTo($config['userModel'], $config['userModelConfig']);
		} elseif (!$this->favoritesTable()->hasAssociation($this->getConfig('userModel'))) {
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
	 * @param array<string, mixed> $options
	 *
	 * @throws \Cake\Http\Exception\MethodNotAllowedException
	 *
	 * @return int|null
	 */
	public function addFavorite(array $options = []) {
		$options += ['value' => null, 'model' => $this->getConfig('model'), 'modelId' => null, 'userId' => null];

		$favorite = $this->favoritesTable()->add($options['model'], $options['modelId'], $options['userId'], $options['value']);
		if (!$favorite->isNew()) {
			return $favorite->id;
		}

		return null;
	}

	/**
	 * Handle adding favorites
	 *
	 * @param array<string, mixed> $options
	 *
	 * @throws \Cake\Http\Exception\MethodNotAllowedException
	 *
	 * @return int
	 */
	public function removeFavorite(array $options = []): int {
		$options += ['model' => $this->getConfig('model'), 'modelId' => null, 'userId' => null];

		return $this->favoritesTable()->remove($options['model'], $options['modelId'], $options['userId']);
	}

	/**
	 * Create the finder favorites
	 *
	 * @param \Cake\ORM\Query\SelectQuery $query
	 * @param array<string, mixed> $options
	 *
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function findFavorites(SelectQuery $query, array $options = []): SelectQuery {
		return $query->contain([
			'Favorites' => function (Query $q) use ($options) {
				$q->contain('Users');
				$q->where(['foreign_key' => $options['id']]);

				return $q;
			},
		]);
	}

	/**
	 * @return \Favorites\Model\Table\FavoritesTable
	 */
	protected function favoritesTable(): FavoritesTable {
		/** @var \Favorites\Model\Table\FavoritesTable */
		return $this->_table->Favorites->getTarget();
	}

}
