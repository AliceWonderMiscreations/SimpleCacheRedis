SimpleCacheRedis
================

An implementation of PSR-16 for Redis including encryption.

This library extends
[\AWonderPHP\SimpleCache\SimpleCache](https://github.com/AliceWonderMiscreations/SimpleCache)
but requires the `devel` branch for updates to the exception classes.

Once the `devel` branch of that project is merged into `master` and an official
release is made, an official release of this library will be made. The current
holdup, I am contemplating also creating a
[memcached](https://www.memcached.org/) release and may need to make some
additional tweaks to the exception classes for that as well.

Though not tagged as an official release, this should be considered stable.

---------------------------------------------------------------------------

This is an implementation of [PSR-16](https://www.php-fig.org/psr/psr-16/) for
the Redis caching engine.

Two different classes are provided. The first just provides a PSR-16 compliant
interface to Redis and the second provides encryption of the cached data via
the libsodium extension.

Please refer to the files [`INSTALL.md`](INSTALL.md), [`USAGE.md`](USAGE.md),
and [`SURVIVAL.md`](SURVIVAL.md) for more information specific to
SimpleCacheRedis.

For instructions specific to the encryption option. see the file
[`SODIUM.md`](SODIUM.md).

Please refer to the file [`LICENSE.md`](LICENSE.md) for the terms of the MIT
License this software is released under.

* [About Redis Caching](#about-redis-caching)
* [About PHP-FIG and PSR-16](#about-php-fig-and-psr-16)
* [Coding Standard](#coding-standard)
* [About AWonderPHP](#about-awonderphp)


About Redis Caching
-------------------

Redis does *far more* than just behave as a caching engine, according to the
web site [https://redis.io](https://readis.io) it provides an in-memory data
structure that can be used as a database, cache, and message broker.

For this PSR-16 implementation, using it as a PHP object cache is all that
matters, but redis itself is capable of some incredible things beyond the scope
of this class.

While looking at various benchmarks, it appears to me that in the use case
scenarios that best match a PSR-16 implementation, APCu is faster than Redis.
However there are several compelling reasons as to why Redis may be the better
choice for some situations:

* Redis cache survives a web server daemon restart.
* Redis cache (unless configured not to) survives a server reboot.
* Redis cache can be made accessible to other servers in your network.
* Redis cache can use a cluster if you have a lot of data you need to have
handled by a caching engine.

If you just have a single server scenario that does not need to share its
cached with other servers, APCu may be the better choice for PSR-16
caching needs, see\
[SimpleCacheAPCu](https://github.com/AliceWonderMiscreations/SimpleCacheAPCu)
for a PSR-16 implementation for APCu.


About PHP-FIG and PSR-16
------------------------

PHP-FIG is the [PHP Framework Interop Group](https://www.php-fig.org/). They
exist largely to create standards that make it easier for different developers
around the world to create different projects that will work well with each
other. PHP-FIG was a driving force behind the PSR-0 and PSR-4 auto-load
standards for example that make it *much much* easier to integrate PHP class
libraries written by other people into your web applications.

The PHP-FIG previously released PSR-6 as a Caching Interface standard but the
interface requirements of PSR-6 are beyond the needs of many web application
developers. KISS - ‘Keep It Simple Silly’ applies for many of us who do not
need some of the features PSR-6 requires.

To meet the needs of those of us who do not need what PSR-6 implements,
[PSR-16](https://www.php-fig.org/psr/psr-16/) was developed and is now an
accepted standard.

When I read PSR-16, the defined interface it was not *that* different from my
own APCu caching class that I have personally been using for years. So I
decided to make my class meet the interface requirements.

Then a Redis user asked me if I could possibly adapt the library for Redis. So
I did and this is the result.


Coding Standard
---------------

The coding standard used is primarily
[PSR-2](https://www.php-fig.org/psr/psr-2/) except with the closing `?>`
allowed, and the addition of some
[PHPDoc](https://en.wikipedia.org/wiki/PHPDoc) requirements largely but not
completely borrowed from the
[PEAR standard](http://pear.php.net/manual/en/standards.php).

The intent is switch PHPDoc standard to
[PSR-5](https://github.com/phpDocumentor/fig-standards/blob/master/proposed/phpdoc.md)
if it ever becomes an accepted standard.

The `phpcs` sniff rules being used: [psr2.phpcs.xml](psr2.phpcs.xml)


About AWonderPHP
----------------

I may become homeless before the end of 2018. I do not know how to survive, I
try but what I try, it always seems to fail. This just is not a society people
like me are meant to be a part of.

If I do become homeless, I fear my mental health will deteriorate at an
accelerated rate and I do not want to witness that happening to myself.

AWonderPHP is my attempt to clean up and package a lot of the PHP classes I
personally use so that something of me will be left behind.

If you wish to help, please see the [SURVIVAL.md](SURVIVAL.md) file.

Thank you for your time.


-------------------------------------------------
__EOF__















