<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Tighten the per-user-per-record uniqueness constraint.
 *
 * The original migration shipped a UNIQUE index on
 * `(model, foreign_key, user_id, value)`. Including `value` is wrong:
 * a row with `value=1` (a like) and a row with `value=-1` (a dislike)
 * for the same `(model, foreign_key, user_id)` both pass the
 * constraint, so a single user could simultaneously be on record as
 * having liked AND disliked the same article. The application-level
 * `findOrCreate` in `FavoritesTable::add()` masks this in the happy
 * path (it queries on `(model, foreign_key, user_id)` and updates
 * `value` in place), but a direct `LikeableBehavior::addLike()`
 * followed by `addDislike()` produces two rows because the constraint
 * doesn't catch it.
 *
 * This drops the four-column unique index and replaces it with a
 * three-column one keyed on `(model, foreign_key, user_id)`, matching
 * the application-level intent. The released migration is left
 * untouched.
 */
class TightenFavoriteUniqueIndex extends BaseMigration {

	public function up(): void {
		$this->table('favorites_favorites')
			->removeIndexByName('favorite_unique')
			->addIndex(
				['model', 'foreign_key', 'user_id'],
				['name' => 'favorite_unique', 'unique' => true],
			)
			->update();
	}

	public function down(): void {
		$this->table('favorites_favorites')
			->removeIndexByName('favorite_unique')
			->addIndex(
				['model', 'foreign_key', 'user_id', 'value'],
				['name' => 'favorite_unique', 'unique' => true],
			)
			->update();
	}

}
