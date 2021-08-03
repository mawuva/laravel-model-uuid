# Laravel Model UUIDs

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mawuekom/laravel-model-uuid.svg?style=flat-square)](https://packagist.org/packages/mawuekom/laravel-model-uuid)
[![Total Downloads](https://img.shields.io/packagist/dt/mawuekom/laravel-model-uuid.svg?style=flat-square)](https://packagist.org/packages/mawuekom/laravel-model-uuid)
![GitHub Actions](https://github.com/mawuva/laravel-model-uuid/actions/workflows/main.yml/badge.svg)

A simple solution to easily work with UUIDs in your Laravel models.

This package provides a way to generate and work with [RFC 4122](https://datatracker.ietf.org/doc/html/rfc4122) version 1, 2, 3, 4, and 5 universally unique identifiers (UUID). It also supports optional and non-standard features, such as [version 6 UUIDs], GUIDs, and other approaches for encoding/decoding UUIDs.

It based on [ramsey/uuid](https://uuid.ramsey.dev/en/latest/index.html) package.
<br>
Take a look on this for more details.

## What Is a UUID?

A universally unique identifier, or UUID, is a 128-bit unsigned integer, usually represented as a hexadecimal string split into five groups with dashes. The most widely-known and used types of UUIDs are defined by [RFC 4122](https://datatracker.ietf.org/doc/html/rfc4122).

A UUID, when encoded in hexadecimal string format, looks like:

```text
ebb5c735-0308-4edc-9aea-8a270aebfe15
```

The probability of duplicating a UUID is close to zero, so they are a great choice for generating unique identifiers in distributed systems.

UUIDs can also be stored in binary format, as a string of 16 bytes.

It is suggested to use UUIDs in your responses instead of IDs, which are
generally enumerable.

This will help mitigate some forms of enumeration attacks.

**Note**: This package explicitly does not disable auto-incrementing on your Eloquent models. In terms of database indexing, it is generally more efficient to use auto-incrementing integers for your internal querying. Indexing your uuid column will make lookups against that column fast, without impacting queries between related models.

## Installation

You can install the package via composer:

```bash
composer require mawuekom/laravel-model-uuid
```

## Usage

To use this package, you simply need to import and use the trait within your Eloquent models.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mawuekom\ModelUuid\Utils\GeneratesUuid;

class Post extends Model
{
    use GeneratesUuid;
}
```

It is assumed that you already have a field named `uuid` in your database, which is used to store the generated value. If you wish to use a custom column name, for example if you want your primary `id` column to be a `UUID`, you can define a `uuidColumn` method in your model.

```php
class Post extends Model
{
    /**
     * The names of the column that should be used for the UUID.
     *
     * @return string
     */
    public function uuidColumn(): string
    {
        return 'custom_column';
    }
}
```

You can have multiple UUID columns in each table by specifying an array in the `uuidColumns` method. When querying using the `whereUuid` scope, the default column - specified by `uuidColumn` will be used.

```php
class Post extends Model
{
    /**
     * The names of the columns that should be used for the UUID.
     *
     * @return array
     */
    public function uuidColumns(): array
    {
        return ['uuid', 'custom_column'];
    }
}
```

By default, this package will use UUID version 4 values, however, you are welcome to use `uuid1`, `uuid3`, `uuid4`, or `uuid5` by specifying the protected property `$uuidVersion` in your model. Should you wish to take advantage of ordered UUID (version 4) values that were introduced in Laravel 5.6, you should specify `ordered` as the `$uuidVersion` in your model.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mawuekom\ModelUuid\Utils\GeneratesUuid;

class Post extends Model
{
    use GeneratesUuid;

    protected $uuidVersion = 'uuid5';
}
```

This trait also provides a query scope which will allow you to easily find your records based on their UUID, and respects any custom field name you choose.

```php
// Find a specific post with the default (uuid) column name
$post = Post::whereUuid($uuid)->first();

// Find multiple posts with the default (uuid) column name
$post = Post::whereUuid([$first, $second])->get();

// Find a specific post with a custom column name
$post = Post::whereUuid($uuid, 'custom_column')->first();

// Find multiple posts with a custom column name
$post = Post::whereUuid([$first, $second], 'custom_column')->get();
```

It also have methods 
 - `getIdFromUuid` : Retrieve auto-incrementing ID from Uuid
 - `loadFromUuid`  : Retrieve model data from Uuid

There is also `ValidatesUuid` trait which as his name says validate incoming UUID before make model queries.

You can use it in your Controller too : 

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Mawuekom\ModelUuid\Utils\ValidatesUuid;

class PostController extends Controller
{
    use ValidatesUuid;

    public function getPostByUuid($uuid)
    {
        $this ->validatesUuid('custom_column', $uuid, Post::class);
    }
}
```

There is also a useful helper function `is_the_given_id_a_uuid` that return `true` if the given id is a uuid or `false` if not.

```php
<?php

$data = is_the_given_id_a_uuid('custom_column', 
'ebb5c735-0308-4edc-9aea-8a270aebfe15'
 Post::class);
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
