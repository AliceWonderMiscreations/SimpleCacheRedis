<?php
declare(strict_types=1);

/**
 * An implementation of the PSR-16 SimpleCache Interface for Redis.
 *
 * @package AWonderPHP/SimpleCacheRedis
 * @author  Alice Wonder <paypal@domblogger.net>
 * @license https://opensource.org/licenses/MIT MIT
 * @link    https://github.com/AliceWonderMiscreations/SimpleCacheRedis
 */
 
// note to self https://www.linode.com/docs/databases/redis/install-and-configure-redis-on-centos-7/

namespace AWonderPHP\SimpleCacheRedis;

/**
 * An implementation of the PSR-16 SimpleCache Interface for Redis.
 *
 * This class implements the [PHP-FIG PSR-16](https://www.php-fig.org/psr/psr-16/)
 *  interface for a cache class.
 *
 * It needs PHP 7.1 or newer and obviously the [Redis PECL](https://pecl.php.net/package/redis) extension.
 */
class SimpleCacheRedis extends \AWonderPHP\SimpleCache\SimpleCache implements \Psr\SimpleCache\CacheInterface
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
        $test = $obj->ping();
        if ($test !== "+PONG") {
            throw \AWonderPHP\SimpleCache\InvalidSetupException::pingNoPongRedis();
        }
        $this->redis = $obj;
        return true;
    }//end setRedisObject()

    /**
     * A wrapper for the actual fetch from the cache.
     *
     * @param string $realKey The internal key used with Redis.
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
        $value = unserialize($serialized);
        return $value;
    }//end cacheFetch()

    /**
     * A wrapper for the actual store of key => value pair in the cache.
     *
     * @param string                        $realKey The internal key used with Redis.
     * @param mixed                         $value   The value to be stored.
     * @param null|int|string|\DateInterval $ttl     The TTL value of this item.
     *
     * @return bool Returns True on success, False on failure.
     */
    protected function cacheStore($realKey, $value, $ttl): bool
    {
        if (is_null($this->redis)) {
            throw \AWonderPHP\SimpleCache\InvalidSetupException::nullRedis();
        }
        $seconds = $this->ttlToSeconds($ttl);
        $serialized = serialize($value);
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
     * Class constructor function. Takes four arguments, three with defaults.
     *
     * @param \Redis $redisObject  The Redis connection object.
     *
     * @param string $webappPrefix (optional) Sets the prefix to use for internal APCu key assignment.
     *                             Useful to avoid key collisions between web applications (think of
     *                             it like a namespace). String between 3 and 32 characters in length
     *                             containing only letters A-Z (NOT case sensitive) and numbers 0-9.
     *                             Defaults to "Default".
     * @param string $salt         (optional) A salt to use in the generation of the hash used as the
     *                             internal APCu key. Must be at least eight characters long. There is
     *                             a default salt that is used if you do not specify. Note that when
     *                             you change the salt, all the internal keys change.
     * @param bool   $strictType   (optional) When set to true, type is strictly enforced. When set to
     *                             false (the default) an attempt is made to cast to the expected type.
     */
    public function __construct($redisObject, $webappPrefix = null, $salt = null, bool $strictType = false)
    {
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
            $this->enabled = true;
        }
        $this->strictType = $strictType;
    }//end __construct()
}//end class

?>