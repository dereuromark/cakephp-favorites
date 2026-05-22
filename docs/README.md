# Favorites Plugin docs

## Types
There are different types:

- **Star**: Binary (set=yes, unset=no) on a record, so either favorite or not. Basically a "like" without being able to "dislike".
  - `StarableBehavior` without any values and `addStar()`/`removeStar()`
- **Like**: Boolean behavior of voting thumbs up/down for "like" vs "dislike".
  - `LikeableBehavior` without any values and `addLike()`/`addDislike()`/`removeLikeOrDislike()`
- **Favorite**: Enum like list of freely defined values between int(-128...128) mapped to a map of `int|string` values or PHP enum and its values.
    - `FavoriteableBehavior` with custom values and `addFavorite()`/`removeFavorite()` as well as defined `values` config.

Star type has a counter-cache option built-in (default false).

## Strategies

There are different main strategies:

- **Controller**: Posting to the plugin Favorites controller with a redirect back to the referer (current view)
- **Action**: Posting to the same action as the favorite info is displayed, e.g. a specific entity view

Each of those can also be done using AJAX instead of normal PRG.

### Controller

Preferred way is posting to the specific controllers using AJAX with PRG fallback.
It has less change of colliding with a different form on that action.

Make sure to set ACL for this controller as only logged in people are allowed to use this.

Also specify a whitelist of models that can be used here in your app config:
```php
'Favorites' => [
    'models' => [
        'star' => [
            'Alias' => 'MyPlugin.MyModel',
        ],
        'like' => [
            'MyPosts' => 'Posts',
        ],
        'favorite' => [
            ...
        ],
    ],
],
```

### Action

This can be needed, if you want to display a validation result on the form itself for invalidation.


## Detailed Docs
- [Star](Star.md)
- [Like](Like.md)
- [Favorite](Favorite.md)

## Database Configuration

### Polymorphic foreign key type

The `favorites_favorites.foreign_key` column type is configurable via the global
`Polymorphic.type` key. This allows apps that use UUID or binary-UUID primary keys
to store matching foreign keys in the favorites table.

```php
// config/app.php or config/app_local.php
Configure::write('Polymorphic.type', 'uuid'); // integer (default) | biginteger | uuid | binaryuuid
```

For `integer` and `biginteger` types, column signedness follows `Migrations.unsigned_primary_keys`
(unsigned when `true`, signed otherwise). For `uuid` and `binaryuuid` types, no signedness option
is applied. The concrete `user_id` column is always `integer` and unaffected by this setting.

## Admin Backend
Go to `/admin/favorites`.

Make sure you set up ACL to only have admins access this part.

Here you can also reset per model type.
