# Custom reactions

Custom favorites store one integer reaction per user, model, and record. They do not provide multiple simultaneous reactions, a PHP enum conversion layer, or a `values` allowlist. The shipped signed tinyinteger column supports values from `-128` through `127`. The helper reserves `0` for its reset icon; use nonzero values for its reactions.

Attach the behavior to your table:

```php
$this->addBehavior('Favorites.Favoriteable');
```

Write or remove a reaction from application code:

```php
$behavior = $posts->getBehavior('Favoriteable');
$behavior->addFavorite(['modelId' => $postId, 'userId' => $userId, 'value' => 4]);
$behavior->removeFavorite(['modelId' => $postId, 'userId' => $userId]);
```

The behavior accepts an integer or `null`; enforce your application's allowed values before saving. The public controller requires a nonzero value, but it does not validate a configured enum or icon map. Authorize each target record before mutation.

## Helper

Load a helper with an explicit icon map:

```php
$this->addHelper('Favorites.Favorites', [
    'icons' => \Favorites\View\Helper\FavoritesHelper::ICONS_GITHUB,
]);
```

For a logged-in user:

```php
echo $this->Favorites->widget('Posts', $post->id);
```

Allow `Posts` in `Favorites.models` and render the `postLink` block outside other forms, as shown in [Getting started](./getting-started). The widget shows available icons when there is no reaction, and a removal control after a reaction is selected. `value()`, `urlAdd()`, and `urlRemove()` support your own UI. Supply a preloaded value to avoid extra lookups in a list.

## Finders

```php
$posts->find('favorites', options: ['id' => $postId]);
$posts->find('favoritesBy', userId: $userId);
```

`favorites` contains the `Favorites` association and its users, optionally filtering associated reactions to the supplied host ID. It does not filter the outer posts query. `favoritesBy` filters host records to those reacted to by the given user.

## Storage shared between types

Stars, likes, and custom reactions use the same table and uniqueness key. Using several types with the same model alias on the same record will share and replace the user's reaction. Use distinct type-specific aliases when you need independent states; see [Configuration](../reference/configuration).
