SimpleCacheRedis Usage
======================

This documentation has the following sections:

1. [Calling the Class](#calling-the-class)
2. [The Class Constructor](#the-class-constructor)
3. [PSR-16 Parameters](#psr-16-parameters)
4. [PSR-16 Methods](#psr-16-methods)
5. [SimpleCacheRedis Specific Methods](#simplecacheredis-specific-methods)
6. [Exceptions](#exceptions)

Please note the PSR-16 related notes here are only partial. To read the full
interface specification, please see the PHP-FIG website:

[https://www.php-fig.org/psr/psr-16/](https://www.php-fig.org/psr/psr-16/)

An [Appendix](#appendix) follows the section on Exceptions.

__NOTE:__ I am sometimes accused of being too verbose when I write
documentation. For less verbose documentation, you can read the code itself.


Calling the Class
-----------------

To interface with Redis, you need to create a Redis object. This class does
not create one for you as there are far too many different configuration
options you may need to specify.

For simple cases where Redis is running on the same server, this usually works:

    $redis = new \Redis();
    $redis->connect('127.0.0.1', 6379);

If you need more complexity than that, see the PECL class documentation.

The easiest way to create an object of the SimpleCacheRedis class:

    use \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis as SimpleCache;
    $CacheObj = new SimpleCache($redis);

Note that the `$redis` argument is passed as the first argument to the
constructorm, it is *required*.

__**NOTE:**__ To use the encrytion enabled version of this class, use the class
`SimpleCacheRedisSodium` instead of `SimpleCacheRedis`. Also, that variant of
this class has a second required argument when creating an instance of the
class, see the [`SODIUM.md`](SODIUM.md) file.


The Class Constructor
---------------------

The class constructor takes four parameters:

1. `$redisObject` -- Required, the connector object to the Redis server.
2. `$webappPrefix` -- defaults to `NULL`
3. `$salt` -- defaults to `NULL`
4. `$strictType` -- defaults to `FALSE`

__**NOTE:**__ To use the encryption enabled version of the class, see the
[`SODIUM.md`](SODIUM.md) file for the constructor instructions.


### The Web Application Prefix

Key collisions are possible when two different web applications or the same web
application on two different virtual hosts running on the same web server use
the same key for different data.

Key collisions are bad. Not only can they potentially screw up how your
application works, but they can potentially leak information.

To solve this problem, this class modifies the key supplied to it to provide a
namespaced internal key to use with Redis.

The Web Application Prefix is an upper case alpha-numeric `[A-Z0-9]` string
that is always used at the beginning of the internal key.

By default, this prefix is `DEFAULT`. You change it by supplying a different
namespace as the second argument when creating an instance of the class:

    $CacheObj = new SimpleCache($redis, 'FOOBAR');

The Web Application Prefix must be at least three characters long and can not
be longer than 32 characters. It can *only* contain upper case letters and the
numbers 0-9, but the class will convert lower case letters to upper case for
you.


### Web Application Salt

The key you supply to the class to store and fetch data with Redis is hashed
and a sixteen or twenty character substring (depending on whether you are
using the Sodium variant or not) of the hex representation of that hash is then
used as part of the internal key.

The benefit of using a hash, I do not have to care what characters are legal to
the actual cache engine. If I want to use a username as part of the key and one
of my users uses something like “バニーガール” as their username, it's cool. I can
feed that to the class and it will hash it to produce a simple ASCII key that
will work with any cache engine.

Hashing also obfuscates the real key, which can be of limited benefit in the
event there is theft of the Redis data store.

Anyway, if you want to specify a custom salt, you may as the third argument:

    $CacheObj = new SimpleCache($redis, 'FOOBAR', 'KmsIs5Q##@6GfpeC@irKIHA');

To specify a custom salt *without* changing the default namespace, just use
`null` as the second argument when creating the class object.


### Strict Types

When you feed a parameter to a function or class method, that parameter has two
properties: The data itself, and the *data type*.

When the data type does not match what the function or method expects, there
are two options:

1. Throw an exception and stop processing.
2. Attempt to recast the data type to what is needed.

You can not always do option 2. You can not recast an array as an integer and
you can not recast an object as a Boolean, for example.

It also can be dangerous to recast. If a function that normally outputs an
integer fails and instead responds with the Boolean type False, that Boolean
False if recast as an integer would equate to 0 which may cause problems if
the code did not check the data type before using it as an integer.

I prefer Option A, to throw an exception when the data type is not what is
expected, but PSR-16 does not specify strict data type checking, so it is an
option that is disabled by default. With the default of false, this class
recasts data types *when I believe it is safe to do so*.

To enable strict data type checking, set the fourth option to true:

    $CacheObj = new SimpleCache($redis, 'FOOBAR', 'KmsIs5Q##@6GfpeC@irKIHA', true);

If you do not wish to alter the Web Application Prefix or Salt, just use `null`
for those parameters.

Note that this fourth parameter is the *only* place where a SimpleCacheRedis
method uses type hinting to let PHP know what type the method expects. This
is because with type hinting, PHP will automatically recast the type if it
can.

Personally I find automatic type casting to be dangerous, so I do not use
parameter type hinting very often.


PSR-16 Parameters
-----------------


### Cache Key

The `key` associated in a `key => value` pair __MUST__ be of the type `string`.

The character `A-Z`, `a-z`, `0-9`, `_`, and `.` __MUST__ be allowed. The
characters `{}()/\@:` __MUST NOT__ be allowed.

Any other characters are up to the implementer to decide. SimpleCacheRedis has
no restrictions on characters other than not allowing what PSR-16 specifically
forbids.

The `key` must be at least one character in length and any key length up to 64
characters must be allowed. SimpleCacheRedis allows key lengths up to 255
characters in length.

The `key` as defined here is referred to as the `$key` parameter when used in
the context of a method or function elsewhere in this documentation.


### Time To Live

This parameter is either an integer specifying the number of seconds for which
the cached value is to be considered valid *or* a valid
[DateInterval](http://php.net/manual/en/class.dateinterval.php) object that
specifies how long the cached value is to ve considered valid

This parameter is referred to as the `$ttl` when used in the context of a
method or function elsewhere in this documentation.


#### SimpleCacheRedis Notes

If an integer is specified as the `$ttl` parameter that is greater than the
current number of seconds since Unix Epoch, this class will assume you are
specifying an intended expiration rather than a TTL that is longer than the
hardware your server is running on will live.

If the TTL evaluates to a time in the past, this class will throw an exception
as that certainly is not what you intend and indicates a bug in your code that
you need to know about.

For the `set` and `setMultiple` methods, the implementation here also accepts
a type `string` if the string can be interpreted by the core PHP `strtotime()`
function.


PSR-16 Methods
--------------

This section describes the methods defined by the PSR-16 interface
specification. These methods will be available in any cache class that
implements PSR-16.


### `$CacheObj->get($key, $default = null);`

This method will attempt to fetch the `value` associated with `key` and will
return it. If there is not a `key => value` pair associated with the `$key`
then the method will return whatever is specified by `$default` and if a
`$default` value is not specified when calling this method, it will return
`null`.


### `$CacheObj->set($key, $value, $ttl = null);`

This method will attempt to set the `key => value` with the specified `$ttl`.
It returns a boolean type `TRUE` on success and `FALSE` on failure.

If the `$ttl` parameter is not specified, the class default TTL will be used.


### `$CacheObj->delete($key);`

This method will attempt to delete the `key => value` pair associated with the
specified `$key`. It returns a Boolean type `TRUE` on success and `FALSE` on
failure.


### `$CacheObj->clear();`

In PSR-16 this method will remove all `key => value` pairs from the cache.

Since SimpleCacheRedis implements a form of namespacing with the Web
Application Prefix, I deviate from PSR-16 in my implementation of this method.
I only clear the records associated with the Web Application Prefix associated
with the object the method is being called from.

If you do not use more than one Web Application Prefix, then it effectively
is the same as clearing them all.

If you do use more than one Web Application Prefix, then clearing them all is
probably *not* what you actually want.

This method returns a Boolean type `TRUE` on success and `FALSE` on failure.


### `$CacheObj->getMultiple($keys, $default = null);`

In this method, the parameter `$keys` __MUST__ be a
[Traversable](https://php.net/manual/en/class.traversable.php) pseudo-type
(a type that can be traversed with the
[foreach](http://php.net/manual/en/control-structures.foreach.php) language
construct).

Usually that means an array, but *some* objects are also traversible.

The method allows you to request many `key => value` pairs at once. It returns
a traversible type with `key => value` using `$default` as the value associated
with keys that are not in the cache. If you do not specify the `$default`
parameter, it defaults to `NULL`.

In the SimpleCacheRedis implementation, it returns a `key => value` array.


### `$CacheObj->setMultiple($values, $ttl = null);`

In this method, the parameter `$values` __MUST__ be a Traversible pseudo-type
containing `key => value` pairs. The `key` __MUST__ be a string that meets the
earlier conditions.

The `$ttl` parameter if specified indicates the TTL that should be used for all
the `key => value` pairs, this method does not support specifying a different
TTL to use depending on the individual pair.

This method returns a Boolean type `TRUE` on success and `FALSE` on failure.


### `$CacheObj->deleteMultiple($keys);`

In this method, the parameter `$keys` __MUST__ be a Traversible pseudo-type
containing keys you want deleted from the cache.

This method returns a Boolean type `TRUE` on success and `FALSE` on failure.


### `$CacheObj->has($key)`

This method checks to see whether or not the specified `$key` has a value
stored in the cache.

If it does, it return a Boolean `TRUE`, otherwise it returns `FALSE`.


SimpleCacheRedis Specific Methods
---------------------------------

In addition to providing the PSR-16 specified methods, this class offers a few
others.


### `$CacheObj->setDefaultSeconds($seconds);`

This allows you to specify the default number of seconds to use for the TTL
when it is not specified in the `set()` or `setMultiple()` methods.

By default, the value is `0` which tells Redis to keep the `key => value`
pair in cache until the server daemon is restarted or until the memory is
needed for something else.

Personally I think that rarely is a good idea. Web applications _SHOULD_
either delete the cached entry or update the cached entry when the data is
no longer valid, but because programmers are human, the code to do that is not
always written or bug free.

Setting the default to something like two weeks (`1209600`) helps reduce the
impact of stale cache.

Regardless of what you set the default to, if `$ttl` is specified when the
`set()` or `setMultiple()` method is called it will __ALWAYS__ override the
default.


### `$cacheObj->clearAll();`

This calls `flushDB()` which will clear all cache entries, regardless of what
the Web Application Prefix is set to. If you use more than one Prefix, this
probably is not what you want to do.

This is the equivalent of the PSR-16 `clear()` method.


### `$cacheObj->getRealKey($key);`

The key the web application uses to interface with SimpleCacheRedis is
different than the key SimpleCacheRedis uses to interface with Redis.

This method returns the internal key SimpleCacheRedis uses to talk to Redis.

It is needed for unit testing.


Exceptions
----------

There are two conditions that can cause SimpleCacheRedis to intentionally throw
an exception:

1. The data type used in a parameter is incorrect
2. The data used in a parameter is not valid for use

### Data Type Exceptions

Exceptions of this type are thrown under one of the following circumstances:

1. You have set `$strictType` to `TRUE` but supplied a parameter that does not
match the expected data type.

2. You do *not* have `$strictType` set to `TRUE` but it would not be safe to
try and recast the data type to what is required.

3. It is not possible to recast the data type to what is required.

This usually indicates a serious problem with your code that needs to be
resolved, your code should not be trying to pass parameters to methods and
functions that are of the wrong type, and when it does, it often indicates an
uncaught error happened somewhere (or it is sloppy code).

Under those circumstances, this class will throw a:

    \AWonderPHP\SimpleCache\StrictTypeException
        extends \TypeError
        implements \Psr\SimpleCache\InvalidArgumentException

Exception.

You can catch these based on `\TypeError` or the PSR implementation.


### Data Invalid Exception

Exceptions of this type are thrown when the data type is valid but the data
itself is not valid. For example, your `$key` may be a valid string but might
contain one of the characters PSR-16 considers to be reserved.

In those circumstances, this class will throw a:

    \AWonderPHP\SimpleCache\InvalidArgumentException
        extends \InvalidArgumentException
        implements \Psr\SimpleCache\InvalidArgumentException

Exception.

You can catch these based on `InvalidArgumentException` or the PSR implementation.


### About Catching Exceptions

Catching Exceptions allows your code to gracefully handle the error. The way to
catch an exception is with a `try{}catch(){}` block:

    try{
        $CacheObj->get($key);
    } catch(\TypeError $e) {
        // code to handle based on $e
    } catch(\InvalidArgumentException $e) {
        // code to handle based on $e
    } catch(\Error $e) {
        // code to handle based on $e
    }

That code takes advantage of which Exception classes in core PHP are extended
by SimpleCacheRedis with the third `catch` block catching Exceptions that are
not extensions of `\TypeError` or `\InvalidArgument`.

The above does not distinguish between Exceptions intentionally thrown by the
class and Exceptions thrown by methods and functions the class calls.

If you wish to do that, then catch the Exceptions based on the PSR-16
interfaces for exceptions before the more generic Exception classes.


APPENDIX
========

* [Extending This Class](#extending-this-class)
* [Unit Testing](#unit-testing)

Extending This Class
--------------------

It is my *opinion* that when a web application implements a third party library
such as this one that is an implementation of a standardized interface such as
PSR-16, the web application should create a class that extends the third party
library.

This way if, for example, I do become homeless and am not able to maintain this
class as bugs are found or as PHP continues to evolve and deprecate ways of
doing things this class uses, you can either modify your extended class to use
a different implementation of PSR-16 or you can modify your extended class to
replace methods in this class that need to be patched.

Only your extended class needs to be modified, the rest of your code then calls
your extended class and continues to work.

     namespace YourCompany/YourProducted/ThirdPartyExtended
     
     class CustomCacheWrapper extends \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis
     {
         public function __construct($redis)
         {
             $MyAppNamespace = 'COOLBANANAS';
             $MySalt = 'pFfbk5fAWppb4zxF6lRprKNcLsgAqV2irUe';
             parent::__construct($redis, $MyAppNamespace, $MySalt, true);
         }
     }

In your web application code where you need an instance of a PSR-16 compliant
cache handler:

    use \YourCompany\YourProducted\ThirdPartyExtended\CustomCacheWrapper as SimpleCache;
    
    $CacheObj = new SimpleCache;

If you then ever need to use a different class for your PSR-16 implementation,
all you need to do is modify the extended class to extend the different
implementation and modify the `__construct` method to suit the new class you
are using.

You can also of course add custom methods of your own.


UNIT TESTING
------------

Unit tests are currently being done with [PHPUnit 7](https://phpunit.de/).

The current results as run on my system are in the file
[UnitTestResults.txt](UnitTestResults.txt)


-------------------------------------------------
__EOF__