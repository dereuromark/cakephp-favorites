<?php

namespace Favorites\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Favorites\Model\Table\FavoritesTable;

class StarableBehavior extends Behavior {

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
		'counterCache' => false,
		'implementedFinders' => [
			'starred' => 'findStarred',
			'starredBy' => 'findStarredBy',
		],
		'fieldCounter' => 'starred_count', //TODO
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

		if ($this->getConfig('counterCache')) {
			$this->favoritesTable()->addBehavior('CounterCache', [
				$this->_table->getAlias() => [$this->getConfig('fieldCounter')],
			]);
		}

		$this->favoritesTable()->belongsTo($this->getConfig('modelClass'), [
			'className' => $this->getConfig('modelClass'),
			'foreignKey' => 'foreign_key',
		]);

		if (!empty($config['userModelConfig'])) {
			$this->favoritesTable()->belongsTo($config['userModelAlias'], $config['userModelConfig']);
		} else {
			$userConfig = [
				'className' => $this->getConfig('userModelClass'),
				'foreignKey' => 'user_id',
				//'counterCache' => true,
			];
			$this->favoritesTable()->belongsTo($this->getConfig('userModel'), $userConfig);
		}
	}

	/**
	 * Handle adding stars
	 *
	 * @param array $options extra information and favorite statistics
	 *
	 * @throws \Cake\Http\Exception\MethodNotAllowedException
	 *
	 * @return int|null
	 */
	public function addStar(array $options = []) {
		$options += ['model' => null, 'modelId' => null, 'userId' => null];

		$favorite = $this->favoritesTable()->add($options['model'], $options['modelId'], $options['userId']);
		if (!$favorite->isNew()) {
			return $favorite->id;
		}

		return null;
	}

	/**
	 * Handle adding stars
	 *
	 * @param array $options extra information and favorite statistics
	 *
	 * @throws \Cake\Http\Exception\MethodNotAllowedException
	 *
	 * @return int
	 */
	public function removeStar(array $options = []): int {
		$options += ['model' => null, 'modelId' => null, 'userId' => null];

		return $this->favoritesTable()->remove($options['model'], $options['modelId'], $options['userId']);
	}

	/**
	 * @param \Cake\ORM\Query\SelectQuery $query
	 * @param array<string, mixed> $options
	 *
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function findStarred(SelectQuery $query, array $options = []): SelectQuery {
		return $query->contain([
			'Favorites' => function (Query $q) use ($options) {
				$q->contain('Users');
				$q->where(['foreign_key' => $options['id']]);

				return $q;
			},
		]);
	}

	/**
	 * @param \Cake\ORM\Query\SelectQuery $query
	 * @param array<string, mixed> $options
	 *
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function findStarredBy(SelectQuery $query, array $options = []): SelectQuery {
		return $query->contain([
			'Favorites' => function (Query $q) use ($options) {
				$q->contain('Users');
				$q->where(['foreign_key' => $options['id'], ['user_id' => $options['userId']]]);

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