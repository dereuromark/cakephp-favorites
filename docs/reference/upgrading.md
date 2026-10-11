# Upgrading

## CakePHP 6 development branch

Follow [Installation](../guide/installation.md) for PHP 8.5 and the development
dependencies. Helpers and behaviors use native `defaultConfig`, `config`, `View`,
`registry` and `table` properties where applicable. Entity field permissions use
`patchable` and marshalling options use `patchableFields`.

The favorites finder takes a named `id` argument. Use
`$posts->find('favorites', id: $id)`; direct behavior calls pass the ID as the
second argument. Overrides of `findFavorites()` must adopt the new
`string|int|null $id = null` parameter. Behavior methods are accessed through
`$posts->getBehavior('Favoriteable')`, since table method forwarding is removed.


Back up the database and read the changes for your installed version before updating. Composer's constraints are the source for supported PHP and CakePHP versions; the [version map](https://github.com/dereuromark/cakephp-favorites/wiki#cakephp-version-map) describes older CakePHP branches.

After updating the package, run the plugin migrations. The tightened unique index allows one reaction per user and record regardless of value. Existing duplicates must be reconciled before that migration can succeed.

Use CakePHP's named finder arguments: `find('starredBy', userId: $userId)`, `find('likedBy', userId: $userId)`, and `find('favorites', id: $postId)`. The `favorites` finder retains its existing options parameter, including direct calls from subclasses.

The `Starred` association is available without a user ID, so list queries can contain it with an explicit user condition. `Liked` and `Favorite` are still created only when a user ID is supplied to the corresponding behavior.

Admin access is denied until `Favorites.adminAccess` returns literal `true` for the request. Check your application's authentication, authorization, and CSRF configuration after an upgrade.


## Stored model keys

Stored model identifiers retain their released defaults. Plugin controllers store the configured table locator target. An explicit behavior `model` remains authoritative. Likeable and Favoriteable default to the table alias. Starable retains its released default of the first matching configured star alias, with the table alias as fallback. Helpers and action components use the model alias supplied by the caller. Reads use exact equality and do not match several stored model keys. Existing rows are not rewritten or reconciled automatically.

Keep matching keys across these paths. For the simple `Posts => Posts` setup, all use `Posts`. For separate relations backed by one class and physical table, retain distinct table locator aliases and matching helper keys. An explicit behavior `model` remains authoritative. [Configuration](./configuration#independent-relations-on-one-table) provides examples.

If an application already used different route and storage names, inspect its existing rows before changing configuration. The plugin cannot infer whether two names represent the same reaction state or different relations. Choose the intended key explicitly; any data conversion belongs in an application migration after checking ownership and uniqueness conflicts.

The helper's existing `fetchModel()`, `modelFactory()`, `getModelType()`, and `setModelType()` APIs remain available. Custom repository factories still supply helper reaction lookups.
