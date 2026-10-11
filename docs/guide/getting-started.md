# Getting started

This example adds stars to existing `Posts` records for logged-in users. Complete [Installation](./installation) first. Your application must already authenticate users and have a `Users` table.

## Allow posts

Add a public alias to `config/app.php`:

```php
'Favorites' => [
    'models' => ['Posts' => 'Posts'],
],
```

The left side is the alias used in URLs and stored in the favorites table. The right side identifies the CakePHP table. Use the same alias in your helpers and behaviors. Do not rename an alias after storing reactions without migrating its stored values.

## Load the helper

In `src/View/AppView.php`, inside `initialize()`:

```php
$this->addHelper('Favorites.Stars');
```

The helper reads the authenticated request identity's `id`. It also supports the TinyAuth `AuthUser` helper and the configured session key, which defaults to `Auth.User.id`. [Configuration](../reference/configuration) explains those alternatives.

## Render the button

In a template that receives a `$post` entity:

```php
<?php if ($this->getRequest()->getAttribute('identity')) { ?>
    <?= $this->Stars->linkIcon('Posts', $post->id) ?>
<?php } ?>
```

The helper creates a POST link and stores its form in the `postLink` view block. Render that block in your application's layout, **outside any other form**:

```php
<?= $this->fetch('postLink') ?>
```

A first click adds a star; the next removes it. No JavaScript library or icon font is required. CakePHP's POST link uses inline JavaScript; adapt the UI if your Content Security Policy prohibits it.

## Check access and persistence

Sign in, open the post, and click the star. Reload the page to check that its state remains selected. Sign out and confirm the button disappears. A direct request to a mutation URL must still pass your controller authentication and authorization rules. Hiding a button does not authorize a request.

The plugin controllers inherit your application's `AppController`. Keep CSRF middleware enabled and configure authorization for the plugin controllers to check that each user may react to the target record. The model allowlist restricts which tables can be selected; it does not check access to individual records.

The helper queries the current user's reaction when no value is supplied. For lists, preload the relevant association and pass the value to avoid one query per button. See [Stars](./stars).
