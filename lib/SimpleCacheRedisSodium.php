<?php
declare(strict_types=1);

/**
 * An implementation of the PSR-16 SimpleCache Interface for Redis that uses AEAD
 * cipher to encrypt the value in the cached key => value pair.
 *
 * @package AWonderPHP/SimpleCacheRedis
 * @author  Alice Wonder <paypal@domblogger.net>
 * @license https://opensource.org/licenses/MIT MIT
 * @link    https://github.com/AliceWonderMiscreations/SimpleCacheRedis
 */

namespace AWonderPHP\SimpleCacheRedis;

/**
 * An implementation of the PSR-16 SimpleCache Interface for APCu that uses AEAD
 * cipher to encrypt the value in the cached key => value pair.
 *
 * This class implements the [PHP-FIG PSR-16](https://www.php-fig.org/psr/psr-16/)
 *  interface for a cache class.
 *
 * It needs PHP 7.1 or newer and obviously the [Redis PECL](https://pecl.php.net/package/redis) extension.
 */
class SimpleCacheRedisSodium extends \AWonderPHP\SimpleCache\SimpleCache implements \Psr\SimpleCache\CacheInterface
{
    /**
     * @var null|\Redis The PECL Redis connector
     */
    protected $redis = null;

    /**
     * Sets the class $redis property or throws an exception.
     *
     * @param mixed $obj The object to validate as \Redis connector object.
     *
     * @return bool Always returns True if no exception thrown
     */
    protected function setRedisObject($obj): bool
    {
        if (! $obj instanceof \Redis) {
            throw \AWonderPHP\SimpleCache\StrictTypeException::redisConnectorExpected($obj);
        }
        $this->redis = $obj;
        return true;
    }//end setRedisObject()

    /**
     * A wrapper for the actual fetch from the cache.
     *
     * @param string $realKey The internal key used with APCu.
     * @param mixed  $default The value to return if a cache miss.
     *
     * @return mixed The value in the cached key => value pair, or $default if a cache miss.
     */
    protected function cacheFetch($realKey, $default)
    {
        if (is_null($this->redis)) {
            throw \AWonderPHP\SimpleCache\InvalidSetupException::nullRedis();
        }
        $serialized = $this->redis->get($realKey);
        if ($serialized === false) {
            return $default;
        }
        $obj = unserialize($serialized);
        return $this->decryptData($obj, $default);
    }//end cacheFetch()

    /**
     * A wrapper for the actual store of key => value pair in the cache.
     *
     * @param string                        $realKey The internal key used with APCu.
     * @param mixed                         $value   The value to be stored.
     * @param null|int|string|\DateInterval $ttl     The TTL value of this item.
     *
     * @return bool Returns True on success, False on failure
     */
    protected function cacheStore($realKey, $value, $ttl): bool
    {
        if (is_null($this->redis)) {
            throw \AWonderPHP\SimpleCache\InvalidSetupException::nullRedis();
        }
        $seconds = $this->ttlToSeconds($ttl);
        try {
            $obj = $this->encryptData($value);
        } catch (\AWonderPHP\SimpleCache\InvalidArgumentException $e) {
            error_log($e->getMessage());
            sodium_memzero($value);
            return false;
        }
        $serialized = serialize($obj);
        if (is_string($value)) {
            sodium_memzero($value);
        }
        // Redis does not treat 0 as forever
        if ($seconds > 0) {
            return $this->redis->set($realKey, $serialized, $seconds);
        } else {
            return $this->redis->set($realKey, $serialized);
        }
    }//end cacheStore()

    /**
     * A wrapper for the actual delete of a key => value pair in the cache.
     *
     * @param string $realKey The key for the key => value pair to be removed from the cache.
     *
     * @return bool Returns True on success, False on failure.
     */
    protected function cacheDelete($realKey): bool
    {
        if (is_null($this->redis)) {
            throw \AWonderPHP\SimpleCache\InvalidSetupException::nullRedis();
        }
        $n = 0;
        if (method_exists($this->redis, 'unlink')) {
            $n = $this->redis->unlink($realKey);
        } else {
            $n = $this->redis->delete($realKey);
        }
        if ($n === 1) {
            return true;
        }
        return false;
    }//end cacheDelete()

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @throws StrictTypeException
     * @throws InvalidArgumentException
     *
     * @return bool True if all items were successfully removed. False if there was an error.
     */
    public function deleteMultiple($keys): bool
    {
        if (! $this->enabled) {
            return false;
        }
        if (is_null($this->redis)) {
            throw \AWonderPHP\SimpleCache\InvalidSetupException::nullRedis();
        }
        $this->checkIterable($keys);
        $keyList = array();
        foreach ($keys as $userKey) {
            if (! is_string($userKey)) {
                throw \AWonderPHP\SimpleCache\StrictTypeException::iterableKeyMustBeString($userKey);
            }
            $keyList[] = $this->adjustKey($userKey);
        }
        $deleted = 0;
        $n = count($keyList);
        if ($n > 0) {
            if (method_exists($this->redis, 'unlink')) {
                $deleted = $this->redis->unlink($keyList);
            } else {
                $deleted = $this->redis->delete($keyList);
            }
        }
        if ($deleted === $n) {
            return true;
        }
        return false;
    }//end deleteMultiple()

    /**
     * Wipes clean the entire cache's keys. This implementation only wipes for matching
     * webappPrefix (custom NON PSR-16 feature set during constructor).
     *
     * @return bool True on success and False on failure.
     */
    public function clear(): bool
    {
        if ($this->enabled) {
            if (is_null($this->redis)) {
                throw \AWonderPHP\SimpleCache\InvalidSetupException::nullRedis();
            }
            $keyList = $this->redis->keys($this->webappPrefix . '*');
            $deleted = 0;
            $n = count($keyList);
            if ($n > 0) {
                if (method_exists($this->redis, 'unlink')) {
                    $deleted = $this->redis->unlink($keyList);
                } else {
                    $deleted = $this->redis->delete($keyList);
                }
            }
            if ($deleted === $n) {
                return true;
            }
        }
        return false;
    }//end clear()

    /**
     * Wipes clean the entire cache's keys regardless of webappPrefix.
     *
     * @return bool True on success and False on failure.
     */
    public function clearAll(): bool
    {
        if ($this->enabled) {
            if (is_null($this->redis)) {
                throw \AWonderPHP\SimpleCache\InvalidSetupException::nullRedis();
            }
            return $this->redis->flushDB();
        }
        return false;
    }//end clearAll()

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @psalm-suppress RedundantCondition
     *
     * @return bool
     */
    public function has($key): bool
    {
        if (is_null($this->redis)) {
            throw \AWonderPHP\SimpleCache\InvalidSetupException::nullRedis();
        }
        $key = $this->adjustKey($key);
        if ($this->enabled) {
            $rs = $this->redis->exists($key);
            // pecl-redis 3.1.6 returns boolean
            if (is_bool($rs)) {
                return $rs;
            }
            // pecl-redis 4.0.0 return integer
            if ($rs === 1) {
                return true;
            }
        }
        return false;
    }//end has()
    
    /**
     * Zeros and then removes the cryptokey from a var_dump of the object.
     * Also removes nonce from var_dump.
     *
     * @return array The array for var_dump().
     */
    public function __debugInfo()
    {
        $result = get_object_vars($this);
        sodium_memzero($result['cryptokey']);
        unset($result['cryptokey']);
        unset($result['nonce']);
        return $result;
    }//end __debugInfo()

    /**
     * Zeros the cryptokey property on class destruction.
     *
     * @return void
     */
    public function __destruct()
    {
        sodium_memzero($this->cryptokey);
    }//end __destruct()
    
    /**
     * The class constructor function
     *
     * @param \Redis $redisObject  The Redis connection object.
     * @param string $cryptokey    This can be the 32-byte key used for encrypting the value,
     *                             a hex representation of that key, or a path to to a
     *                             configuration file that contains the key.
     * @param string $webappPrefix (optional) Sets the prefix to use for internal APCu key
     *                             assignment. Useful to avoid key collisions between web
     *                             applications (think of it like a namespace). String between
     *                             3 and 32 characters in length containing only letters A-Z
     *                             (NOT case sensitive) and numbers 0-9. Defaults to
     *                             "Default".
     * @param string $salt         (optional) A salt to use in the generation of the hash used
     *                             as the internal APCu key. Must be at least eight characters
     *                             long. There is a default salt that is used if you do not
     *                             specify. Note that when you change the salt, all the
     *                             internal keys change.
     * @param bool   $strictType   (optional) When set to true, type is strictly enforced.
     *                             When set to false (the default) an attempt is made to cast
     *                             to the expected type.
     *
     * @psalm-suppress RedundantConditionGivenDocblockType
     */
    public function __construct($redisObject, $cryptokey, $webappPrefix = null, $salt = null, $strictType = null)
    {
        $this->checkForSodium();
        if (is_string($cryptokey)) {
            $end = substr($cryptokey, -5);
            $end = strtolower($end);
            if ($end === ".json") {
                $config = $this->readConfigurationFile($cryptokey);
                sodium_memzero($cryptokey);
            }
        }
        // use config file but override when argument passed to constructor not null
        if (! isset($config)) {
            $config = new \stdClass;
        }
        if (isset($config->hexkey)) {
            $cryptokey = $config->hexkey;
            sodium_memzero($config->hexkey);
        } // else setCryptoKey should fail, will have to test...
        if (is_null($webappPrefix)) {
            if (isset($config->prefix)) {
                $webappPrefix = $config->prefix;
            }
        }
        if (is_null($salt)) {
            if (isset($config->salt)) {
                $salt = $config->salt;
            }
        }
        if (! is_null($strictType)) {
            if (! is_bool($strictType)) {
                $strictType = true;
            }
        }
        if (is_null($strictType)) {
            if (isset($config->strict)) {
                if (is_bool($config->strict)) {
                    $strictType = $config->strict;
                }
            }
        }
        if (is_null($strictType)) {
            $strictType = false;
        }
        $this->strictType = $strictType;
        
        if ($this->setRedisObject($redisObject)) {
            $invalidTypes = array('array', 'object', 'boolean');
            if (! is_null($webappPrefix)) {
                if (! $strictType) {
                    $type = gettype($webappPrefix);
                    if (! in_array($type, $invalidTypes)) {
                        $webappPrefix = (string)$webappPrefix;
                    }
                }
                $this->setWebAppPrefix($webappPrefix);
            }
            if (! is_null($salt)) {
                if (! $strictType) {
                    $type = gettype($salt);
                    if (! in_array($type, $invalidTypes)) {
                        $salt = (string)$salt;
                    }
                }
                $this->setHashSalt($salt);
            }
            $this->setCryptoKey($cryptokey);
        }
        sodium_memzero($cryptokey);
    }//end __construct()
}//end class

?>