# Favorites Plugin docs

## Types
There are different types:

- **Favorite**: Binary (set=yes, unset=no) on a record, so either favorite or not.

TODO:
- **Like**: Boolean behavior of voting thumbs up/down for "like" vs "dislike".
- **Custom**: Enum like list of possible values between int(-128...128) mapped to a int|string value.

## Strategies

There are different main strategies:

- **Controller**: Posting to the plugin Favorites controller with a redirect back to the referer (current view)

TODO:
- **Action**: Posting to the same action as the favorite info is displayed, e.g. a specific entity view

Each of those can also be done using AJAX instead of normal PRG.

### Controller

Preferred way is posting to the specific controllers using AJAX with PRG fallback.
It has less change of colliding with a different form on that action.

Make sure to set ACL for this controller as only logged in people are allowed to use this.

### Action

This can be needed, if you want to display a validation result on the form itself for invalidation.

## Admin Backend
Go to `/admin/favorites`.

Make sure you set up ACL to only have admins access this part.
