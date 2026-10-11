# Configuration

Configuration is read from `Favorites` in CakePHP's `Configure`. Per-helper and per-behavior options are merged with the global values. Component `sessionKey` can override the global session key.

## Model aliases

A flat map is available to all reaction types:

```php
'Favorites' => [
    'models' => ['Posts' => 'Posts'],
],
```

Type-specific maps take precedence over matching flat aliases:

```php
'Favorites' => [
    'models' => [
        'star' => ['StarPosts' => 'StarPosts'],
        'like' => ['VotePosts' => 'VotePosts'],
        'favorite' => ['ReactPosts' => 'ReactPosts'],
    ],
],
```

Use matching helper/URL aliases and table locator aliases. Plugin controllers store the configured target string; helpers and action components use the alias supplied by the caller. An explicit behavior `model` option is authoritative. Likeable and Favoriteable default to the table alias. Starable retains its released default: the first matching star alias from the configured map, or the table alias when there is no match. Set its `model` explicitly when several aliases point to one target.

The type-specific example needs table locator entries for `StarPosts`, `VotePosts`, and `ReactPosts`. Giving three public aliases the same target `Posts` does not separate controller storage: all three would store `Posts`.

## Independent relations on one table

Register distinct table locator aliases before using the plugin. Both can instantiate the same table class and access the same physical table:

```php
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

$locator = TableRegistry::getTableLocator();
$locator->setConfig('WishlistedPosts', ['className' => 'Blog.Posts']);
$locator->setConfig('BookmarkedPosts', ['className' => 'Blog.Posts']);

Configure::write('Favorites.models', [
    'WishlistedPosts' => 'WishlistedPosts',
    'BookmarkedPosts' => 'BookmarkedPosts',
]);
```

Here `Blog.Posts` is an existing table class for the physical `posts` table. Both logical instances must attach the relevant behavior; for example, `Favoriteable` may be added in that class's `initialize()`. Favoriteable's default `model` becomes `WishlistedPosts` or `BookmarkedPosts` according to the locator alias. When attaching Starable, set `model` explicitly to the intended relation key to avoid selecting the first configured star alias.

Pass the same logical key to the helper:

```php
echo $this->Favorites->widget('WishlistedPosts', $post->id);
echo $this->Favorites->widget('BookmarkedPosts', $post->id);
```

The two reactions remain independent for the same user and post. Associations and finders read only their configured model key, and removal affects only that key. For direct behavior calls, use the corresponding logical table instance.

For Likeable and Favoriteable, a qualified locator alias such as `Blog.Posts` defaults the stored model to the unqualified `Posts`. Starable first checks its configured alias map. If all paths should instead use a qualified storage key, set the behavior's `model` explicitly and use matching helper and map keys:

```php
$this->addBehavior('Favorites.Favoriteable', ['model' => 'Blog.Posts']);
Configure::write('Favorites.models', ['Blog.Posts' => 'Blog.Posts']);
```

Keep existing stored keys when upgrading. There is no automatic alias conversion or lookup across several stored model names. See [Upgrading](./upgrading#stored-model-keys).

## User identity

Controllers, components, and helpers recognize the authenticated request identity through its ArrayAccess `id` field. `Favorites.userIdField` changes the field name. Identity IDs must be positive integers or decimal integer strings within PHP's integer range. Other values are treated as unauthenticated because the favorites schema uses an integer `user_id`.

Legacy lookups also support configured values, TinyAuth's `AuthUser`, the controller's `Auth` component, and session data. The default session key is `Auth.User`, giving `Auth.User.id`. For CakeDC/Users session storage, configure `sessionKey => 'Auth'` if your application uses `Auth.id`. A session key is only useful when the application actually stores the user there; the Authentication plugin need not do so.

`Favorites.userModelClass` defaults to `Users`. Behaviors accept `userModelConfig` for custom associations. Host records may use other primary key types, but the shipped users foreign key remains integer.

## Database foreign keys

Before the first migration, set:

```php
'Polymorphic' => [
    'type' => 'uuid',
],
```

The default is `integer`; `biginteger`, `uuid`, and `binaryuuid` can be used when supported by your database and Migrations adapter. Signedness for integer foreign keys follows `Migrations.unsigned_primary_keys`. This affects new tables only. Existing tables need an application migration to alter their column type.

The unique index covers `model`, `foreign_key`, and `user_id`. `created` records creation; there is no `modified` column. Multiple reaction types share this key unless you give them different aliases.

## Helper output

All three helpers create POST forms in the `postLink` block. Render that block from the layout. The default strategy posts to the plugin controller; `strategy => 'action'` posts to the current URL and needs the corresponding component.

The model map is an allowlist for plugin controllers. Action components do not require that map and retain the posted alias as their storage key. The host application must validate allowed aliases, target records and authorization before processing those requests.

Only stars provide a built-in CounterCache option. AJAX transport is an application concern: helpers do not ship a JavaScript client, and the plugin controllers redirect after mutations. Component AJAX callbacks expose view variables; configure your own response rendering when using them.
