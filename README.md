# Laravel Model Status

A very simple yet very integrated Laravel status package (now using native enums, no database required)

## Status

[![latest tag](https://img.shields.io/github/v/tag/open-southeners/laravel-model-status?label=latest&sort=semver)](https://github.com/open-southeners/laravel-model-status/releases/latest) [![packagist version](https://img.shields.io/packagist/v/open-southeners/laravel-model-status)](https://packagist.org/packages/open-southeners/laravel-model-status) [![required php version](https://img.shields.io/packagist/php-v/open-southeners/laravel-model-status)](https://www.php.net/supported-versions.php) [![run-tests](https://github.com/open-southeners/laravel-model-status/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/open-southeners/laravel-model-status/actions/workflows/tests.yml) [![phpstan](https://github.com/open-southeners/laravel-model-status/actions/workflows/phpstan.yml/badge.svg)](https://github.com/open-southeners/laravel-model-status/actions/workflows/phpstan.yml) [![Laravel Pint](https://img.shields.io/badge/code%20style-pint-orange?logo=laravel)](https://github.com/open-southeners/laravel-model-status/actions/workflows/pint.yml) [![Codacy Badge](https://app.codacy.com/project/badge/Grade/5ad437ad01d34189968c6e79630be88b)](https://www.codacy.com/gh/open-southeners/laravel-model-status/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=open-southeners/laravel-model-status&amp;utm_campaign=Badge_Grade) [![Codacy Badge](https://app.codacy.com/project/badge/Coverage/5ad437ad01d34189968c6e79630be88b)](https://www.codacy.com/gh/open-southeners/laravel-model-status/dashboard?utm_source=github.com&utm_medium=referral&utm_content=open-southeners/laravel-model-status&utm_campaign=Badge_Coverage) [![Edit on VSCode online](https://img.shields.io/badge/vscode-edit%20online-blue?logo=visualstudiocode)](https://vscode.dev/github/open-southeners/laravel-model-status)

## Getting started

```
composer require open-southeners/laravel-model-status
```

### Create status enum

Imaging you've a `Post` model, you should then create an enum like `PostStatus` that might look like this one:

```php
use OpenSoutheners\LaravelModelStatus\ModelStatus;

enum PostStatus: int implements ModelStatus
{
    case Draft = 1;

    case Published = 2;

    case Hidden = 3;
}
```

Now remember to import `ModelStatus` interface and use it in the enum.

### Setup your model

Adding 3 things: The `ModelStatuses` PHP attribute, implementing `Statusable` interface to your class and using `HasStatuses` trait. 

```php
use OpenSoutheners\LaravelModelStatus\Attributes\ModelStatuses;
use OpenSoutheners\LaravelModelStatus\HasStatuses;
use OpenSoutheners\LaravelModelStatus\Statusable;

// Remember to replace PostStatus::class with whatever the enum you are using
// Also second option is a boolean that enable/disable events
#[ModelStatuses(PostStatus::class, true)]
class Post extends Model implements Statusable
{
    use HasStatuses;
}
```

### Available methods

#### setStatus

```php
$post = new Post();

// Set status to post instance
$post->setStatus(PostStatus::Published);

// Set status to post instance and persist to DB
$post->setStatus(PostStatus::Published, true);
```

#### setStatusWhen

```php
// Set status to post instance only when current status is "Draft"
$post->setStatusWhen(PostStatus::Draft, PostStatus::Published);

// Set status to post instance and persist to DB only when current status is "Draft"
$post->setStatusWhen(PostStatus::Draft, PostStatus::Published, true);
```

#### hasStatus

```php
// Check current status is "Published"
$post->hasStatus(PostStatus::Published);

// Check current status is "Published" as string (type sensitive)
$post->hasStatus('Published');
```

#### withoutStatusEvents

```php
// Whenever you save the model updates and don't want to trigger events
Post::withoutStatusEvents(fn () => $post->setStatus(PostStatus::Published));
```

### Model events

Right now this package offers a `OpenSoutheners\LaravelModelStatus\Events\StatusSwapped` event that you can use to get changes performed with `setStatusWhen` method.
