# Upgrading

Back up the database and read the changes for your installed version before updating. Composer's constraints are the source for supported PHP and CakePHP versions; the [version map](https://github.com/dereuromark/cakephp-favorites/wiki#cakephp-version-map) describes older CakePHP branches.

After updating the package, run the plugin migrations. The tightened unique index allows one reaction per user and record regardless of value. Existing duplicates must be reconciled before that migration can succeed.

Use CakePHP's named finder arguments: `find('starredBy', userId: $userId)`, `find('likedBy', userId: $userId)`, and `find('favorites', id: $postId)`. The current custom finder APIs avoid deprecated positional options arrays in application calls.

The controller and behavior model maps now resolve type-specific aliases consistently. Earlier plugin controllers may have stored qualified names such as `Blog.Posts`; earlier behaviors defaulted to the table alias, such as `Posts`. New rows use the configured public alias. See the legacy storage configuration below before updating an application with existing reactions.

The `Starred` association is available without a user ID, so list queries can contain it with an explicit user condition. `Liked` and `Favorite` are still created only when a user ID is supplied to the corresponding behavior.

Admin access is denied until `Favorites.adminAccess` returns literal `true` for the request. Check your application's authentication, authorization, and CSRF configuration after an upgrade.


## Existing behavior aliases

Legacy model names require an explicit mapping because the plugin cannot infer which table or reaction state owns an old row. For example, an application using both `Blog.Posts` and `Shop.Posts` cannot safely assign an unqualified `Posts` row automatically. Several public aliases for one table can also represent separate reaction states.

Back up the database and inspect existing rows before enabling a mapping. Confirm that every listed legacy name belongs exclusively to the target public alias, including rows written by behaviors on tables absent from `Favorites.models`. Keep distinct aliases for independent stars, likes and favorites. A legacy name cannot also be an active public alias or be assigned to several mappings.

For an application where all `Blog.Posts` and `Posts` reactions belong to `article`:

```php
Configure::write('Favorites.models', ['article' => 'Blog.Posts']);
Configure::write('Favorites.legacyModels', [
    'article' => ['Blog.Posts', 'Posts'],
]);
```

Configure this before loading behaviors. Helpers, components, associations and finders then include those stored names. Updates reuse an existing row, and remove/reset include the mapped names. New rows still use `article`. An update also removes duplicate mapped rows for that user and record in the same transaction.

Reconcile existing duplicates before enabling the mapping: a public alias row and a legacy row for the same user and record can otherwise produce inconsistent finder results or duplicate associations until that reaction is updated. Choose the intended value and retain one row. Ambiguous rows need manual assignment rather than a shared mapping.

A data migration is an alternative. For an application where every `Posts` reaction should use `article`:

```sql
UPDATE favorites_favorites SET model = 'article' WHERE model = 'Posts';
```

Check for uniqueness conflicts first. An explicit behavior `model => 'Posts'` option remains available for applications that intentionally retain their original storage name.
