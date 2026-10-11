# Upgrading

Back up the database and read the changes for your installed version before updating. Composer's constraints are the source for supported PHP and CakePHP versions; the [version map](https://github.com/dereuromark/cakephp-favorites/wiki#cakephp-version-map) describes older CakePHP branches.

After updating the package, run the plugin migrations. The tightened unique index allows one reaction per user and record regardless of value. Existing duplicates must be reconciled before that migration can succeed.

Use CakePHP's named finder arguments: `find('starredBy', userId: $userId)`, `find('likedBy', userId: $userId)`, and `find('favorites', id: $postId)`. The current custom finder APIs avoid deprecated positional options arrays in application calls.

The controller and behavior model maps now resolve type-specific aliases consistently. Existing records saved under full table names such as `Blog.Posts` need an application data migration if the public alias is `Articles`. Do not update those values blindly if several aliases point to the same table.

The `Starred` association is available without a user ID, so list queries can contain it with an explicit user condition. `Liked` and `Favorite` are still created only when a user ID is supplied to the corresponding behavior.

Admin access is denied until `Favorites.adminAccess` returns literal `true` for the request. Check your application's authentication, authorization, and CSRF configuration after an upgrade.

This audit targets the current CakePHP 5 APIs. It does not claim CakePHP 6 compatibility before that framework's API and the plugin's dependency constraints support it.
