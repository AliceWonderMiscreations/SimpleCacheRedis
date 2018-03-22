SimpleCacheRedis Installation
=============================

You need to have the [Redis](https://redis.io/) server installed and available.
If you are using Linux a suitable version is *probably* packaged in your
distribution software repository.

You need to have the [PECL Redis](https://pecl.php.net/package/redis) extension
installed. I have tested with version 3.1.6 in PHP 7.1 and have used
[travis-ci](https://travis-ci.org/) to test version 4.0.0 in PHP 7.1 and PHP
7.2.

SimpleCacheRedis is a library, it is not a stand alone application.

If you want to use it in a project that is managed by
[Composer](https://getcomposer.org/), make sure the following is within your
`composer.json` file:

    "require": {
        "awonderphp/simplecacheredis": "^1.0"
    },

As long as your `composer.json` allows the [Packagist](https://packagist.org/)
repository, that should pull in this library when you run the command:

    composer install


Manual Installation
-------------------

For manual installation, there are two other libraries you must install where
your autoloader can find them first:

1. [`psr/simple-cache`](https://github.com/php-fig/simple-cache/tree/master/src)
2. [`AWonderPHP/SimpleCache`](https://github.com/AliceWonderMiscreations/SimpleCache/)

Both of those libraries include exception classes that also must be installed
where your autoloader can find them.

Once those two dependencies are installed, there are two class files:

1. [`SimpleCacheRedis`](lib/SimpleCacheRedis.php)
2. [`SimpleCacheRedisSodium`](lib/SimpleCacheRedisSodium.php)

The first class provides PSR-16 without encryption, the second provides PSR-16
with encryption.

Both files use the namespace `AWonderPHP\SimpleCacheRedis`.


RPM Installation
----------------

I have started a project called
[PHP Composer Class Manager](https://github.com/AliceWonderMiscreations/php-ccm)
but it is not yet ready for deployment, and as of today (March 19 2018) it
will likely be awhile.


Class Usage
-----------

Please see the file [`USAGE.md`](USAGE.md) for class usage.


-------------------------------------------------
__EOF__