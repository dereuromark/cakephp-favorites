# Like type

- **Like**: Boolean behavior of voting thumbs up/down for "like" vs "dislike".
    - `LikeableBehavior` without any values and `addLike()`/`addDislike()`/`removeLikeOrDislike()`

## Quick-Setup

You can either fetch the record yourself and pass it to the view to display.
Or you can use the convenience helper to do all that for you.

Minimal and out of the box setup:
```php
// in your AppView::initialize()
$this->addHelper('Favorites.Likes');
```

In your entity view, wherever you want to display the icons as post link, you can fetch the value or URL:
```php
$value = $this->Likes->value($alias, $id);
$urlLike = $this->Likes->urlLike($alias, $id);
$urlDislike = $this->Likes->urlDislike($alias, $id);
```
This uses the TinyAuth.AuthUser helper, but you can also use Identity or any other means to check for logged in user.

Display it any way you see fit.
Now add some AJAX on top to further improve usability.
