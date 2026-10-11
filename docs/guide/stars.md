# Stars

## Behavior API

Attach the behavior in your table's `initialize()` method:

```php
$this->addBehavior('Favorites.Starable');
```

Write a star from trusted application code using the authenticated user's ID:

```php
$behavior = $posts->getBehavior('Starable');
$behavior->addStar(['modelId' => $postId, 'userId' => $userId]);
$behavior->removeStar(['modelId' => $postId, 'userId' => $userId]);
```

`addStar()` returns the saved reaction ID, or `null` when the returned entity is not saved. Storage failures can throw an ORM persistence exception. `removeStar()` returns the number of deleted rows. Never take the user ID directly from form data.

Use the named finder argument with CakePHP's current ORM:

```php
$posts->find('starred');
$posts->find('starredBy', userId: $userId);
```

`starred` means starred by any user. `starredBy` filters to one user.

## Preload the current user's star

`Starred` is available once the behavior is attached. Without a behavior `userId` setting, explicitly filter it for the current user:

```php
$posts->find()->contain([
    'Starred' => fn ($query) => $query->where(['Starred.user_id' => $userId]),
]);
```

In the template:

```php
echo $this->Stars->linkIcon('Posts', $post->id, (bool)$post->starred);
```

The third argument avoids a helper lookup. Do not contain an unfiltered `Starred` association to represent the current user's state.

## Counters

Add an integer `starred_count` column with a default of zero to your host table, then enable:

```php
$this->addBehavior('Favorites.Starable', ['counterCache' => true]);
```

The behavior configures CakePHP's CounterCache behavior for the host association. Adding and removing stars uses entity callbacks to update the counter. Admin reset uses `deleteAll()`, which bypasses those callbacks. Recalculate counters after a reset. Existing stars also need an initial counter backfill.

## Display options

The default helper uses the `★` character. Load Font Awesome separately if you select its FA6 template:

```php
$this->addHelper('Favorites.Stars', [
    'html' => \Favorites\View\Helper\StarsHelper::HTML_TYPE_FA6,
    'colorMap' => [0 => '#666', 1 => '#b45309'],
]);
```

You can pass your own HTML template or disable inline colors with `colorMap => false` and style the `star` and `starred` classes.

## Same-action forms

For a controller that receives star submissions on its own action, set its default table and load `Favorites.Starable`. Configure the helper with `strategy => 'action'`. Processing defaults to `startup`, not `beforeRender`. Set `on => 'beforeRender'` and `useEntity => true` only when the action places the target entity in its expected view variable first. Restrict the component to the intended `actions` and avoid mixing unrelated forms with its payload.

The separate plugin controller is the simpler starting point. [Getting started](./getting-started) covers that route.
