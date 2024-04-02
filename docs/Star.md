# Star type

- **Star**: Binary (set=yes, unset=no) on a record, so either favorite or not. Basically a "like" without being able to "dislike".
  - `StarableBehavior` without any values and `addStar()`/`removeStar()`

## Quick-Setup

You can either fetch the record yourself and pass it to the view to display.
Or you can use the convenience helper to do all that for you.

Minimal and out of the box setup:
```php
// in your AppView::initialize()
$this->addHelper('Favorites.Stars');
```

In your entity view, wherever you want to display the icon as post link:
```php
<?php if ($this->AuthUser->id()) { ?>
    <span class="star-form"><?php echo $this->Stars->linkIcon('Events', $event->id)?></span>
<?php } ?>
```
This uses the TinyAuth.AuthUser helper, but you can also use Identity or any other means to check for logged in user.

It by default uses:
- FA6 icon `fa-solid fa-star`
- colors grey and orange for not starred vs starred

You can customize the HTML and colors on the helper.

- First click: Star
- Second click: Unstar

Now add some AJAX on top to further improve usability.
