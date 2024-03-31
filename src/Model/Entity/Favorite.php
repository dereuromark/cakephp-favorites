<?php

namespace Favorites\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $model
 * @property int $foreign_key
 * @property int|null $user_id
 * @property \Cake\I18n\DateTime $created
 * @property \App\Model\Entity\User|null $user
 */
class Favorite extends Entity {

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array<string, bool>
	 */
	protected array $_accessible = [
		'*' => true,
		'id' => false,
	];

}
