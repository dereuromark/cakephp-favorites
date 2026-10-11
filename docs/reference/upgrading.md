# Upgrading

Back up the database and read the changes for your installed version before updating. Composer's constraints are the source for supported PHP and CakePHP versions; the [version map](https://github.com/dereuromark/cakephp-favorites/wiki#cakephp-version-map) describes older CakePHP branches.

After updating the package, run the plugin migrations. The tightened unique index allows one reaction per user and record regardless of value. Existing duplicates must be reconciled before that migration can succeed.

Use CakePHP's named finder arguments: `find('starredBy', userId: $userId)`, `find('likedBy', userId: $userId)`, and `find('favorites', id: $postId)`. The current custom finder APIs avoid deprecated positional options arrays in application calls.

The controller and behavior model maps now resolve type-specific aliases consistently. Existing records saved under full table names such as `Blog.Posts` or table aliases such as `Posts` remain accessible through the configured public alias when the old name identifies one target table. Updates reuse those records without changing their stored model name.

The `Starred` association is available without a user ID, so list queries can contain it with an explicit user condition. `Liked` and `Favorite` are still created only when a user ID is supplied to the corresponding behavior.

Admin access is denied until `Favorites.adminAccess` returns literal `true` for the request. Check your application's authentication, authorization, and CSRF configuration after an upgrade.


## Existing behavior aliases

Earlier `Likeable` and `Favoriteable` behaviors defaulted their stored `model` to the table alias. They now resolve the configured public alias. With `Articles => Posts`, old behavior rows may therefore use `Posts`, while new rows use `Articles`. Earlier plugin controllers may instead have saved a qualified table name such as `Blog.Posts`.

Legacy lookups are disabled for an ambiguous name, for example `Posts` when both `Blog.Posts` and `Shop.Posts` are configured. Qualified names remain usable when they identify one target. A public alias must also avoid another model's historical table alias: with `Articles => Blog.Articles` and a second target `Shop.Articles`, a stored `Articles` row cannot identify its original table. Inspect and assign those rows manually before using that public alias. If the same public alias maps to different targets for different reaction types, legacy fallback is also disabled.

Choose one alias for each reaction state. A data migration is optional for unambiguous mappings, but required to recover ambiguous rows. Back up the database, inspect the existing rows, and check for uniqueness conflicts before converting them. For an application where every `Posts` reaction should use `Articles`:

```sql
UPDATE favorites_favorites SET model = 'Articles' WHERE model = 'Posts';
```

Adapt both values to your application. Do not run this conversion when the old alias contains several reaction states that need separate aliases. You can temporarily preserve behavior lookups with an explicit `model => 'Posts'` behavior option while planning the data migration.
