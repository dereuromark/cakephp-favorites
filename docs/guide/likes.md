# Likes and dislikes

A like stores `1`, a dislike stores `-1`, and removing a vote deletes the row. One user has one reaction per model and record; switching a vote updates that reaction.

Attach `Favorites.Likeable` to the host table:

```php
$this->addBehavior('Favorites.Likeable');
```

The behavior methods are:

```php
$behavior = $posts->getBehavior('Likeable');
$behavior->addLike(['modelId' => $postId, 'userId' => $userId]);
$behavior->addDislike(['modelId' => $postId, 'userId' => $userId]);
$behavior->remove(['modelId' => $postId, 'userId' => $userId]);
```

The removal method is `remove()`, not `removeLikeOrDislike()`. Use the following finders:

```php
$posts->find('liked');
$posts->find('disliked');
$posts->find('likedBy', userId: $userId);
```

`liked` and `disliked` select records with any matching vote. `likedBy` selects records liked by a specific user.

## Helper

Allow the model in `Favorites.models`, as in [Getting started](./getting-started), and load:

```php
$this->addHelper('Favorites.Likes');
```

For a logged-in user:

```php
echo $this->Likes->widget('Posts', $post->id);
```

Render the layout's `postLink` block outside other forms. The widget offers like and dislike buttons when there is no vote, and removal when a vote exists. Use `value()` to fetch the current value, or `urlLike()` and `urlDislike()` to build your own forms.

Attach the behavior with `userId` if you want its `Liked` association, and pass the preloaded value to `widget()` in lists. Unlike `Starred`, that association is only created when a nonempty `userId` is supplied.

## Same-action processing

`Favorites.Likeable` is also a component name. Load it in the host controller and configure the helper with `strategy => 'action'` when you need the same action to process the vote. Its default callback is `startup`. Keep the application responsible for authentication, record authorization, and CSRF protection.
