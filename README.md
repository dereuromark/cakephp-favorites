#  CakePHP Favorites Plugin

[![CI](https://github.com/dereuromark/cakephp-favorites/actions/workflows/ci.yml/badge.svg?branch=cake6)](https://github.com/dereuromark/cakephp-favorites/actions?query=workflow%3ACI+branch%3Acake6)
[![Coverage Status](https://img.shields.io/codecov/c/github/dereuromark/cakephp-favorites/cake6.svg)](https://app.codecov.io/github/dereuromark/cakephp-favorites/tree/cake6)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://phpstan.org/)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-favorites/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-favorites)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.5-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-favorites/license.png)](https://packagist.org/packages/dereuromark/cakephp-favorites)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-favorites/d/total.svg)](https://packagist.org/packages/dereuromark/cakephp-favorites)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-purple.svg?style=flat-square)](https://github.com/php-fig-rectified/fig-rectified-standards)

Favorites, stars, votes, and custom reactions for CakePHP applications.

The plugin stores one reaction per user, model alias, and record. Behaviors provide the storage API; helpers create POST links for the plugin controllers or your own action.

- **Stars:** add or remove a mark, with an optional stored counter.
- **Likes:** store a like or dislike and remove a vote.
- **Custom reactions:** map integer values to application-defined icons.

This development branch targets CakePHP 6 and PHP 8.5. Read the
[branch installation guide](docs/guide/installation.md) and
[Getting started](docs/guide/getting-started.md). The
[published website](https://dereuromark.github.io/cakephp-favorites/) covers CakePHP 5.

The [version map](https://github.com/dereuromark/cakephp-favorites/wiki#cakephp-version-map) covers older framework branches. For numeric ratings, use [CakePHP Ratings](https://github.com/dereuromark/cakephp-ratings). Try the [sandbox examples](https://sandbox.dereuromark.de/sandbox/favorite-examples).
