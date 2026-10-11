# Administration

The plugin's backend is available at `/admin/favorites` when its routes are enabled. It inherits your application's `AppController` and requires `Favorites.adminAccess` to be a closure that returns literal `true`. Missing configuration, other return values, or exceptions deny access.

For an application whose authenticated identity exposes a `roles` field:

```php
'Favorites' => [
    'adminAccess' => function (\Cake\Http\ServerRequest $request): bool {
        $identity = $request->getAttribute('identity');

        return $identity !== null && in_array('admin', (array)$identity->roles, true);
    },
],
```

Adapt the role check to your application's actual identity. Keep its normal authentication configuration in place. The callback does not log a user in.

The index lists stored model names and supports resetting reactions for a configured model through a POST request. The listing page contains the configured `Users` association and allows individual deletions. Reset deletes rows in bulk; it does not recalculate host CounterCache columns.

The backend templates currently expect a flat `Favorites.models` mapping for model names in reset controls. Use separate application administration if you configure only nested type-specific mappings. Do not assume that a model appearing in a type-specific frontend mapping is automatically supported by the reset screen.
