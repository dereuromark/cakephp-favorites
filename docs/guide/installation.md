# Installation

This development branch requires CakePHP 6 and PHP 8.5. Migrations currently
uses the upstream CakePHP 6 compatibility branch.

Install the plugin in an existing CakePHP application:

```bash
composer config minimum-stability dev
composer config prefer-stable true
composer require cakephp/cakephp:^6.0@dev dereuromark/cakephp-favorites:dev-cake6
bin/cake plugin load Favorites
```

Composer checks the package's PHP and CakePHP constraints. See [composer.json](https://github.com/dereuromark/cakephp-favorites/blob/cake6/composer.json) for the current supported versions.

## Create the table

If your application does not already use the Migrations plugin, install and load it:

```bash
composer require cakephp/migrations:dev-fix/6.x-cakephp-compatibility
bin/cake plugin load Migrations
```

Run the plugin migrations against your application's configured database:

```bash
bin/cake migrations migrate -p Favorites
```

This creates `favorites_favorites` and its indexes. Back up an existing database before applying migrations. The migration that tightens uniqueness requires existing data to have at most one row per model, record, and user. Resolve duplicate rows before upgrading.

The plugin expects a `Users` table with integer primary keys. Configure `Favorites.userModelClass` if your table has another alias. UUID host records are supported through `Polymorphic.type`; UUID user IDs are not supported by the shipped schema. Set the polymorphic type before the first migration, as described in [configuration](../reference/configuration).

## Load configuration

Add the settings you need to `config/app.php` or another file loaded into `Configure`. The plugin does not automatically load `config/app.example.php`. Use that file as a reference, with values chosen for your application.

Plugin routes are enabled when the plugin is loaded normally. Protect the public reaction controllers through your application's authentication and authorization configuration. The admin routes also require an explicit access callback.

Continue with [Getting started](./getting-started) for a complete star button.
