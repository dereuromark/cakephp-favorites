#  CakePHP Favorites Plugin

[![CI](https://github.com/dereuromark/cakephp-favorites/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/dereuromark/cakephp-favorites/actions?query=workflow%3ACI+branch%3Amaster)
[![Coverage Status](https://img.shields.io/codecov/c/github/dereuromark/cakephp-favorites/master.svg)](https://app.codecov.io/github/dereuromark/cakephp-favorites/tree/master)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://phpstan.org/)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-favorites/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-favorites)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-favorites/license.png)](https://packagist.org/packages/dereuromark/cakephp-favorites)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-favorites/d/total.svg)](https://packagist.org/packages/dereuromark/cakephp-favorites)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-purple.svg?style=flat-square)](https://github.com/php-fig-rectified/fig-rectified-standards)

Favorites, stars, votes, and custom reactions for CakePHP applications.

The plugin stores one reaction per user, model alias, and record. Behaviors provide the storage API; helpers create POST links for the plugin controllers or your own action.

- **Stars:** add or remove a mark, with an optional stored counter.
- **Likes:** store a like or dislike and remove a vote.
- **Custom reactions:** map integer values to application-defined icons.

Read the **[documentation website](https://dereuromark.github.io/cakephp-favorites/)**, starting with [Installation](https://dereuromark.github.io/cakephp-favorites/guide/installation) and [Getting started](https://dereuromark.github.io/cakephp-favorites/guide/getting-started).

The [version map](https://github.com/dereuromark/cakephp-favorites/wiki#cakephp-version-map) covers older framework branches. For numeric ratings, use [CakePHP Ratings](https://github.com/dereuromark/cakephp-ratings). Try the [sandbox examples](https://sandbox.dereuromark.de/sandbox/favorite-examples).
