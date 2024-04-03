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

- First click: Star
- Second click: Unstar

Now add some AJAX on top to further improve usability.

It by default uses
- UTF8 `★` character.
- colors grey and orange for not starred vs starred.

You can also set `html` to `StarsHelper::HTML_TYPE_FA6` for FA6 icon `fa-solid fa-star` template.
Or just provide your own HTML snippet.

You can customize colors on the helper for the built-in types, or use CSS classes.

## Action strategy
When using the same action to post to, make sure to not have search/save or other operations that can interfere.
The processing here happens in the `beforeRender()` callback by default.

If needed, you can set the callback to `startup()` or even `beforeFilter()` execution.
In this case, you cannot use entities, and you must rely on the data being sent via form data (model, id, ...).

```php
// in your controller initialize()
$this->loadComponent('Favorites.Starable');
```
Should be all that is needed for most cases.

The component will
- auto-add the corresponding behavior and load needed associations
- will provide a hook for posted data to be stored and redirect back using PRG pattern

For the time being you have to manually pass down the favorites data to the view or let the view helper fetch it.
On pages with multiple entities, letting the helper do a separate SQL query for each is not advised, though.
For basic view pages this is fine.
