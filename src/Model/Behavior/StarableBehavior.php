<?php

namespace Favorites\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Behavior;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Favorites\Model\Table\FavoritesTable;
use Favorites\Utility\Config;

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
		'fieldCounter' => 'starred_count',
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
			$modelAlias = Config::alias($this->_table->getRegistryAlias(), Config::TYPE_STAR) ?: $this->_table->getAlias();
			$this->setConfig('model', $modelAlias);
		}
		if (!$this->getConfig('modelClass')) {
			$this->setConfig('modelClass', $this->_table->getRegistryAlias());
		}
		if (!$this->getConfig('userModel')) {
			[, $userModelAlias] = pluginSplit($this->getConfig('userModelClass'));
			$this->setConfig('userModel', $userModelAlias);
		}

		$this->_table->hasMany('Favorites', [
			'className' => $this->getConfig('favoriteClass'),
			'foreignKey' => 'foreign_key',
			'order' => 'Favorites.created DESC',
			'conditions' => ['Favorites.model' => $this->getConfig('model')],
			'dependent' => true,
		]);

		if (!empty($config['userId'])) {
			$this->_table->hasOne('Starred', [
				'className' => $this->getConfig('favoriteClass'),
				'foreignKey' => 'foreign_key',
				'conditions' => ['Starred.model' => $this->getConfig('model'), 'Starred.user_id' => $config['userId']],
				'dependent' => true,
			]);
		}

		if ($this->getConfig('counterCache')) {
			$this->favoritesTable()->addBehavior('CounterCache', [
				$this->_table->getAlias() => [
					$this->getConfig('fieldCounter') => [
						'conditions' => ['Favorites.model' => $this->getConfig('model')],
					],
				],
			]);
		}

		$this->favoritesTable()->belongsTo($this->getConfig('modelClass'), [
			'className' => $this->getConfig('modelClass'),
			'foreignKey' => 'foreign_key',
		]);

		if ($this->getConfig('userModelConfig') && !$this->favoritesTable()->hasAssociation($this->getConfig('userModel'))) {
			$this->favoritesTable()->belongsTo($this->getConfig('userModel'), $this->getConfig('userModelConfig'));
		} elseif (!$this->favoritesTable()->hasAssociation($this->getConfig('userModel'))) {
			$userConfig = [
				'className' => $this->getConfig('userModelClass'),
				'foreignKey' => 'user_id',
				//'counterCache' => true,
			];
			$this->favoritesTable()->hasOne($this->getConfig('userModel'), $userConfig);
		}
	}

	/**
	 * Handle adding stars
	 *
	 * @param array<string, mixed> $options
	 *
	 * @throws \Cake\Http\Exception\MethodNotAllowedException
	 *
	 * @return int|null
	 */
	public function addStar(array $options = []) {
		$options += ['model' => $this->getConfig('model'), 'modelId' => null, 'userId' => null];

		$favorite = $this->favoritesTable()->add($options['model'], $options['modelId'], $options['userId']);
		if (!$favorite->isNew()) {
			return $favorite->id;
		}

		return null;
	}

	/**
	 * Handle adding stars
	 *
	 * @param array<string, mixed> $options
	 *
	 * @throws \Cake\Http\Exception\MethodNotAllowedException
	 *
	 * @return int
	 */
	public function removeStar(array $options = []): int {
		$options += ['model' => $this->getConfig('model'), 'modelId' => null, 'userId' => null];

		return $this->favoritesTable()->remove($options['model'], $options['modelId'], $options['userId']);
	}

	/**
	 * @param \Cake\ORM\Query\SelectQuery $query
	 *
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function findStarred(SelectQuery $query): SelectQuery {
		$subQuery = $this->buildStarredQuerySnippet();
		$modelAlias = $this->_table->getAlias();

		return $query->where([$modelAlias . '.id IN' => $subQuery]);
	}

	/**
	 * @param \Cake\ORM\Query\SelectQuery $query
	 * @param int $userId
	 *
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function findStarredBy(SelectQuery $query, int $userId): SelectQuery {
		$subQuery = $this->buildStarredByQuerySnippet($userId);
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
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	protected function buildStarredQuerySnippet(): SelectQuery {
		$model = $this->getConfig('model');
		$conditions = [
			$this->favoritesTable()->getAlias() . '.model' => $model,
		];

		return $this->favoritesTable()->find()
			->select($this->favoritesTable()->getAlias() . '.foreign_key')
			->where($conditions);
	}

	/**
	 * @param int $userId
	 *
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	protected function buildStarredByQuerySnippet(int $userId): SelectQuery {
		$model = $this->getConfig('model');
		$conditions = [
			$this->favoritesTable()->getAlias() . '.model' => $model,
			$this->favoritesTable()->getAlias() . '.user_id' => $userId,
		];

		return $this->favoritesTable()->find()
			->select($this->favoritesTable()->getAlias() . '.foreign_key')
			->where($conditions);
	}

}
