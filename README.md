# pickset
A collection of PHP utilities to "unlock" various capabilities in your 
applications. `pickset` is comprised of several classes:

* `DatabaseConnection` - a PDO connection wrapper, implemented as a singleton

* `DateUtils` - methods for converting and working with dates and epoch values

* `Logger` - a text file logging facility, implemented as a singleton

* `TextUtils` - methods for parsing and manipulating text strings

## Requirements

* PHP 7.1 or better; some functions now have nullable return type declarations 
which aren't supported in older PHP versions.

## Installation

You can install `pickset` either with or without Composer.

### With Composer

Use Composer to require `pickset` into your project.

```bash
[user@host]$ composer require parseword/pickset
```

Require Composer's autoloader, alias whichever classes you want with the `use` 
statement for convenience, and go to town.

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

use parseword\pickset\{
    DateUtils
};

echo 'The first second of today was ' . DateUtils::firstSecondOfDay() . PHP_EOL;
```

### Without Composer

Use git to clone `pickset` into a subdirectory of your project.

```bash
[user@host]$ git clone https://github.com/parseword/pickset.git pickset
```

In your code, you'll need to import the manual autoloader before you can use 
any of the classes, e.g.

```php
<?php
require_once 'pickset/autoload-surrogate.php';

use parseword\pickset\{
    TextUtils
};

echo '3409873325 bytes is ' . TextUtils::bytesToHuman(3409873325) . PHP_EOL;
```

## Usage

There's no thorough documentation yet outside of the code comments. I needed 
to get this onto packagist quickly to import it from some other projects.

* `Logger` class documentation: [README-Logger.md](https://github.com/parseword/pickset/blob/master/README-Logger.md)

I'll add more documentation as time permits.
