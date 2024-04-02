#  CakePHP Favorites Plugin

[![CI](https://github.com/dereuromark/cakephp-favorites/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/dereuromark/cakephp-favorites/actions?query=workflow%3ACI+branch%3Amaster)
[![Coverage Status](https://img.shields.io/codecov/c/github/dereuromark/cakephp-favorites/master.svg)](https://app.codecov.io/github/dereuromark/cakephp-favorites/tree/master)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://phpstan.org/)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-favorites/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-favorites)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-favorites/license.png)](https://packagist.org/packages/dereuromark/cakephp-favorites)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-favorites/d/total.svg)](https://packagist.org/packages/dereuromark/cakephp-favorites)

Favorites plugin for CakePHP applications.

This branch is for use with **CakePHP 5.0+**. For details see [version map](https://github.com/dereuromark/cakephp-favorites/wiki#cakephp-version-map).

## Motivation

The old Favorites plugins don't seem to be supported anymore:
- https://github.com/CakeDC/favorites (Cake 2)
- Similar to https://github.com/aschelch/cakephp-like-plugin (Cake 2)

This plugin aims to merge and revive them as modern CakePHP 5.x plugin.
Hopefully we can have all the features back up and working soon.

## Features

"Favorites" lets people express how they feel about some content.
Make any model reactable in a minutes!

There are many implementations in modern applications:

- Starred a.k.a GitHub stars (and remove star)
- GitHub Reactions
- Facebook Reactions
- YouTube Likes
- Slack Reactions
- Reddit Votes
- Medium Claps

This package so far mainly supports basic and binary favorite per entry in 3 different types:
- **star** (yes/no)
- **like** (upvote/downvote/none)
- **favorite** (custom enum list)

But it could be developed in mind that it should cover all the possible use cases and
be viable in enterprise applications including multiple reactions per entity.
Help is appreciated.

A "counter-cache" field can be put on the starred record itself, to more easily count the
"stars" without having to calculate it at runtime.

For ratings (e.g. 1-5 star range) use https://github.com/dereuromark/cakephp-ratings instead.

### Install, Setup, Usage
See the **[Docs](docs/README.md)** for details.
