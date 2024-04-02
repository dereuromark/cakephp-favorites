# Favorite type

- **Favorite**: Enum like list of freely defined values between int(-128...128) mapped to a map of `int|string` values or PHP enum and its values.
    - `FavoriteableBehavior` with custom values and `addFavorite()`/`removeFavorite()` as well as defined `values` config.

## Quick-Setup

You can either fetch the record yourself and pass it to the view to display.
Or you can use the convenience helper to do all that for you.

Minimal and out of the box setup:
```php
// in your AppView::initialize()
$this->addHelper('Favorites.Favorites');
```

In your entity view, wherever you want to display the icons as post link, you can fetch the value or URL:
```php
$value = $this->Favorites->value($alias, $id);
$urlAdd = $this->Favorites->urlAdd($alias, $id);
$urlRemove = $this->Favorites->urlRemove($alias, $id);
```
This uses the TinyAuth.AuthUser helper, but you can also use Identity or any other means to check for logged in user.

Display it any way you see fit.
Now add some AJAX on top to further improve usability.
