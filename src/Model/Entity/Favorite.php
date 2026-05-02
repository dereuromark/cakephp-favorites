<?php

namespace Favorites\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $model
 * @property int $foreign_key
 * @property int $user_id
 * @property int|null $value
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\ORM\Entity $user
 */
class Favorite extends Entity {

	/**
	 * Fields that can be mass assigned via patchEntity() / newEntity().
	 *
	 * Foreign keys (`user_id`, `model`, `foreign_key`) and `created` are explicitly NOT
	 * mass-assignable — letting them through would allow a host application that ever exposes
	 * a Favorite-shaped CRUD endpoint, fixture factory, or admin form to forge favorites on
	 * behalf of any user against any record (privilege-escalation primitive — Issue #2).
	 *
	 * Only the user's actual choice (`value`) is mass-assignable; the rest must be set
	 * server-side via `FavoritesTable::add($model, $foreignKey, $userId, $value)`.
	 *
	 * @var array<string, bool>
	 */
	protected array $_accessible = [
		'value' => true,
		'id' => false,
		'user_id' => false,
		'model' => false,
		'foreign_key' => false,
		'created' => false,
	];

}
