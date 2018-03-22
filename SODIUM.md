SimpleCacheRedisSodium Class Usage
==================================

The `SimpleCacheRedisSodium` class provides AEAD encryption/decryption of the
`value` portion of the `key => value` pairs cached in Redis.

* [Basic Requirements](#basic-requirements)
* [The Constructor](#the-constructor)
* [JSON Encoded Configuration File](#json-encoded-configuration-file)
* [`makeRedisSodiumConfig` Utility](#makeredissodiumconfig-utility)
* [Encryption Dirty Work](#encryption-dirty-work)
* [Performance](#performance)
* [Security Appendix](#security-appendix)


Basic Requirements
------------------

If you are running PHP 7.1 you will need to install the
[PECL libsodium](https://pecl.php.net/package/libsodium) extension. If you are
running PHP 7.2, the libsodium functions are now a standard part of PHP.
Starting with 7.2, you should not need to do anything.


The Constructor
---------------

The constructor is the only part of using SimpleCacheRedisSodium that differs
from using SimpleCacheRedis.

The SimpleCacheRedis constructor takes one mandatory and three optional arguments:

* `@param \Redis $redisObject` (required)
* `@param string $webappPrefix`
* `@param string $salt`
* `@param bool   $strictType`

For an explanation of those parameters, see the [`USAGE.md`](USAGE.md)
documentation.

SimpleCacheRedisSodium adds an additional parameter, and it is required:

* `@param \Redis $redisObject` (required)
* `@param string $cryptokey` (required)
* `@param string $webappPrefix`
* `@param string $salt`
* `@param bool   $strictType`

The required `$cryptokey` parameter can be one of three things:

1. Binary string representation of a 32 byte integer (not screen printable)
2. Hex string representation of a 32 byte integer (64 characters `[0-9][a-f]`)
3. Full path on the filesystem to a JSON encoded configuration file that
contains a hex string representing the 32 byte integer.

The 32 byte integer is the encryption key, so it needs to be properly
generated:

    $secret = random_bytes(32);

The `random_bytes()` function is available in PHP 7 and newer, and uses a
cryptographically secure random generator. See
[random_bytes](https://php.net/manual/en/function.random-bytes.php) for
more information on the security of that function.

That will generate a binary representation of the 32 byte integer.

    $hexkey = sodium_bin2hex($secret);
    sodium_memzero($secret);

That turn the binary representation of the 32 byte integer into a hexadecimal
representation of the integer, and then completely overwrite the reference to
`$secret` in memory with zeros.

`sodium_bin2hex` is used instead of `bin2hex` because of some possible side
channel attacks on the `bin2hex` command that could leak information.


### JSON Encoded Configuration File

This is a configuration file that contains the hex representation of the secret
and optionally the other options to the constructor. An example:

    {
        "hexkey": "f42f663e72f74b9e852b172df7f57ff4ab42e505167116e13dacd0d1daf00e77"
        "prefix": "WEBMAIL",
        "salt": "C%76462%f5AQ0D(9B656#4Df591d?#A4(1A1#D#0;Dc3FF6FF85AE@3#(46@7C!C",
        "strict": true,
    }

In the configuration file, the keyword `hexkey` is used to reference the
hexadecimal representation of the secret.

The optional keyword `prefix` is used to define the `$webappPrefix` parameter
to the constructor. If defined both here *and* in your call to the class
constructor, the call to the constructor takes precedence. If present at all
it must be a string containing at least three alphanumeric characters but not
more than 32.

The optional keyword `salt` is used to define the `$salt` parameter to the
constructor. If defined both here *and* in your call to the class
constructor, the call to the constructor takes precedence. If present at all
it must be a string containing at least eight characters.

The optional keyword `strict` is used to define the `$strictType` parameter to
the constructor. If defined both here *and* in your call to the class
constructor, the call to the constructor takes precedence. If present at all
it must be a Boolean, which in JSON is *always* lower case `true` or lower case
`false`.


### `makeRedisSodiumConfig` Utility

A PHP shell script is provided that will create the configuration file for you.
This is the `makeRedisSodiumConfig` utility. It does use some functions that
require PHP 7 (e.g. `random_int`) so if the first `php` executable in your
`PATH` environment is not at least PHP 7, then you will need to change:

    #!/usr/bin/env php

to the full path of your PHP 7 install. For most people, it should just work.
For people using enterprise server distributions who have PHP 7 installed in a
custom location and/or have an older PHP install earlier in the path, the above
shebang may not find the right PHP version.

The script takes two optional arguments:

* `$argv[1] string setting for $webappPrefix`
* `$argv[2] bool   setting for $strictType`

It uses defaults if not supplied (`DEFAULT` for `$argv[1]` and `false` for
`$argv[2]`)

The utility script will generate the secret key and the salt itself.

The output of the command will be named `${webappPrefix}.json` if you supplied
an argument to the script and `DEFAULT.json` if you did not.

The output of the command will be in the same directory you called the command
from. If a file already exists with the needed file name, it will use the
current UNIX timestamp to back up the existing file, e.g. `DEFAULT.json` would
be renamed to something like `DEFAULT-1520150241.json` and then `DEFAULT.json`
will be recreated.

You can edit the resulting configuration file to change what the values in it
or name the file whatever you want. Just makes sure it *always* has a `hexkey`
entry that contains 64 hex characters (`[0-9][a-f]`) from a cryptographically
secure pseudo-random number generator.

You can then call the constructor in your web application like this:

    use \AWonderPHP\SimpleCacheRedis\SimpleCacheRedisSodium as SimpleCache;
    $CacheObj = new SimpleCache($redis, '/path/to/WEBMAIL.json');

The file needs to be readable by the web server but should not be readable by
other users on the system. On CentOS/RHEL systems which Apache:

    chown apache:apache WEBMAIL.json && chmod 0400 WEBMAIL.json

should accomplish that task.

The file should also not be in a directory served by the web server.


### Changing the Secret Key

Like any secret key used for validation or encryption, it is a good idea to
periodically change the secret. Changing the secret will *effectively* clear
the Redis cache of all previous `key => value` pairs that were encrypted with
the old secret, requests for them will result in a cache miss.

The best time to change the Secret is probably when you are doing maintenance
that requires restarting the web server daemon.


Encryption Dirty Work
---------------------

This class was designed so that the programmer does not have to worry about
any of the dirty work. The only thing you have to do is generate a persistent
secret, and I have even made that easy.

Encryption is performing using the PHP wrapper to
[libsodium](https://doc.libsodium.org/)

Excellent document to the PHP libsodium wrapper can be found at the
[Paragon Initiative](https://paragonie.com/book/pecl-libsodium) website.

The SimpleCacheRedis class uses one of two ciphers:

* AES-256-GCM
* IETF variant ChaCha20+Poly1305

If your server processors supports the
[AES-NI](https://en.wikipedia.org/wiki/AES_instruction_set) instructions (most
at this point in time do), AES-256-GCM will be used as it is the faster of the
two *when AES-NI is supported by the CPU*. Otherwise ChaCha20+Poly1305 is used.

Both of those ciphers use a 32-byte secret key and a 12-byte
[nonce](https://en.wikipedia.org/wiki/Cryptographic_nonce).

Both of those ciphers are very high quality ciphers and are part of the soon to
be finalized TLS 1.3 specification. They are also very fast. The class will
decide which one to use automatically based upon processor support.

With respect to the nonce, a too common mistake (e.g. the Sony Playstation 3
mistake) is reusing the same nonce for more than one encryption session.

The SimpleCacheRedisSodium class makes sure the nonce has been incremented
before encrypting a value, and if something is seriously broken resulting in
failure to increment the nonce, an exception is thrown rather than having a
potentially dangerous encryption take place.

The only thing the system administrator needs to worry about is generating the
secret in such a way that it can be reused each time the class is instantiated.


Performance
-----------

I have not performed any kind of benchmarks, but it will be slower than Redis
without encrypting the data first. How much slower, I do not know, but most
servers run on modern Intel Xeon processors with the AES-NI instruction set
that give hardware acceleration to the AES-256-GCM cipher.

I do *not* believe the encryption and decryption of cached data will be the
performance bottleneck in your web application, not on relatively modern
hardware anyway.



Security Appendix
=================

Secret Key Security
-------------------

The purpose of caching data is to be able to allow your web application to
retrieve it on demand. In order to accomplish this, the same secret key used to
encrypt the data *must* be available to the web application to decrypt the data
when it has retrieved it from the cache.

You could hard code the secret key into your web application, like is often
done with SQL passwords, but I recommend against it.

PHP caches an opcode compiled version of scripts that it loads. This means your
secret key would be retrievable from that opcode cache if an exploit exists
that allows the attacker to retrieve it.

I do not *believe* PHP caches the contents of files read with
`file_get_contents()` (I could be wrong about that) so storing the secret key
in a configuration file is safer.

The class reads the file into a JSON string and then converts it to an object.
The JSON string is then zeroed out using `sodium_memzero()` function.

Once they key has been verified as an actual working key, the key is set as a
class property and the copy of the key in the object is zeros out.

A copy of the key however continues to live as a property of the object.

The class `__destruct` method will zero it out when the class is destroyed, but
as long as the class is active, a copy of the secret key exists in memory and
thus is potentially vulnerable to key theft. With multiple instances of the
class instantiated at the same time, there will be multiple copies of the
secret in memory.

Point is, do not use this class to store things like bank account numbers in
the Redis cache because it is possible that if an attacker manages to get a dump
of the server memory, it may contain data that should not be exposed.

This class makes it harder for someone who gets a dump of the server memory to
obtain the decrypted information, but it is still possible.

Things like bank account numbers should not even be store on servers with a
public IP address anyway.


-------------------------------------------------
__EOF__