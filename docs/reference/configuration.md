# Configuration

Configuration is read from `Favorites` in CakePHP's `Configure`. Per-helper and per-behavior options are merged with the global values. Component `sessionKey` can override the global session key.

## Model aliases

A flat map is available to all reaction types:

```php
'Favorites' => [
    'models' => ['Articles' => 'Blog.Posts'],
],
```

Type-specific maps take precedence over matching flat aliases:

```php
'Favorites' => [
    'models' => [
        'star' => ['StarPosts' => 'Posts'],
        'like' => ['VotePosts' => 'Posts'],
        'favorite' => ['ReactPosts' => 'Posts'],
    ],
],
```

Use these public aliases in helper calls. The plugin controllers resolve the table through the relevant type's map and store the alias. Behaviors detect a configured alias from their registry name, or use the table alias when none is configured. Set their `model` option explicitly if several aliases point to the same table.

Changing aliases in an existing installation requires updating stored `model` values. Old installations may contain fully qualified table names saved by earlier plugin controllers; normalize those rows to your chosen alias before using the new helper flow.

## User identity

Controllers, components, and helpers recognize the authenticated request identity through its ArrayAccess `id` field. `Favorites.userIdField` changes the field name. The ID is cast to an integer because the favorites schema uses an integer `user_id`.

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

Only stars provide a built-in CounterCache option. AJAX transport is an application concern: helpers do not ship a JavaScript client, and the plugin controllers redirect after mutations. Component AJAX callbacks expose view variables; configure your own response rendering when using them.
