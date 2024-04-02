<?php

namespace Favorites\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Favorites\Model\Entity\Favorite;

/**
 * Favorites Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \Favorites\Model\Entity\Favorite newEmptyEntity()
 * @method \Favorites\Model\Entity\Favorite newEntity(array $data, array $options = [])
 * @method array<\Favorites\Model\Entity\Favorite> newEntities(array $data, array $options = [])
 * @method \Favorites\Model\Entity\Favorite get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Favorites\Model\Entity\Favorite findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \Favorites\Model\Entity\Favorite patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Favorites\Model\Entity\Favorite> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Favorites\Model\Entity\Favorite|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Favorites\Model\Entity\Favorite saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Favorites\Model\Entity\Favorite>|false saveMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Favorites\Model\Entity\Favorite> saveManyOrFail(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Favorites\Model\Entity\Favorite>|false deleteMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Favorites\Model\Entity\Favorite> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FavoritesTable extends Table {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		$this->setTable('favorites_favorites');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		$this->belongsTo('Users');
	}

	/**
	 * Validations rules
	 *
	 * @param \Cake\Validation\Validator $validator validator
	 *
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): Validator {
		$validator->notEmptyString('model');
		$validator->requirePresence('model', 'create');
		$validator->notEmptyString('foreign_key');
		$validator->requirePresence('foreign_key', 'create');

		return $validator;
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 *
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules): RulesChecker {
		$rules->add($rules->existsIn(['user_id'], 'Users'));

		return $rules;
	}

	/**
	 * @param string $model
	 * @param int $foreignKey
	 * @param int $userId
	 * @param int|null $value
	 *
	 * @return \Favorites\Model\Entity\Favorite
	 */
	public function add(string $model, int $foreignKey, int $userId, ?int $value = null): Favorite {
		$data = [
			'model' => $model,
			'foreign_key' => $foreignKey,
			'user_id' => $userId,
		];

		$favorite = $this->findOrCreate($data);
		if ($favorite->hasErrors()) {
			return $favorite;
		}

		$favorite->value = $value;

		$this->saveOrFail($favorite);

		return $favorite;
	}

	/**
	 * @param string $model
	 * @param int $foreignKey
	 * @param int $userId
	 *
	 * @return int
	 */
	public function remove(string $model, int $foreignKey, int $userId): int {
		$data = [
			'model' => $model,
			'foreign_key' => $foreignKey,
			'user_id' => $userId,
		];

		return $this->deleteAll($data);
	}

	/**
	 * @param string $model
	 *
	 * @return int
	 */
	public function reset(string $model): int {
		return $this->deleteAll(['model' => $model]);
	}

}
