<?php
declare(strict_types=1);

/**
 * Unit testing for SimpleCacheRedis Exceptions Strict Mode.
 *
 * @package AWonderPHP/SimpleCacheRedis
 * @author  Alice Wonder <paypal@domblogger.net>
 * @license https://opensource.org/licenses/MIT MIT
 * @link    https://github.com/AliceWonderMiscreations/SimpleCacheRedis
 */

use PHPUnit\Framework\TestCase;

/**
 * Test class for SimpleCache strict no encryption.
 */
// @codingStandardsIgnoreLine
final class SimpleCacheRedisStrictExceptionTest extends TestCase
{
    /**
     * The test object.
     *
     * @var \AWonderPHP\SimpleCache\SimpleCache
     */
    private $testStrict;
    
    /**
     * The redis object
     *
     * @var \Redis
     */
    private $redis;

    /**
     * PHPUnit Setup, create an instance of SimpleCacheRedis.
     *
     * @return void
     */
    public function setUp()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
        $this->redis->flushDB();
        $this->testStrict = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, null, null, true);
    }//end setUp()

    /**
     * Check to see if Redis is even possible.
     *
     * @return void
     */
    public function testCanWeEvenAccessRedisFromTestEnvironment(): void
    {
        $test = $this->redis->ping();
        $this->assertEquals("+PONG", $test);
    }//end testCanWeEvenAccessRedisFromTestEnvironment()

    /* type error tests */

    /**
     * Feed float data when setting the default TTL. Strict only test.
     *
     * @psalm-suppress InvalidScalarArgument
     *
     * @return void
     */
    public function testDefaultTtlInvalidTypeFloat(): void
    {
        $ttl = 55.55;
        $this->expectException(\TypeError::class);
        $this->testStrict->setDefaultSeconds($ttl);
    }//end testDefaultTtlInvalidTypeFloat()

    /**
     * Use integer as key. Strict test only.
     *
     * @psalm-suppress InvalidScalarArgument
     *
     * @return void
     */
    public function testCacheKeyInvalidTypeInteger(): void
    {
        $value = '99 bottles of beer on the wall';
        $key = 67;
        $this->expectException(\TypeError::class);
        $this->testStrict->set($key, $value);
    }//end testCacheKeyInvalidTypeInteger()

    /**
     * Use float as key. Strict test only.
     *
     * @psalm-suppress InvalidScalarArgument
     *
     * @return void
     */
    public function testCacheKeyInvalidTypeFloat(): void
    {
        $value = '99 bottles of beer on the wall';
        $key = 67.99412;
        $this->expectException(\TypeError::class);
        $this->testStrict->set($key, $value);
    }//end testCacheKeyInvalidTypeFloat()

    /**
     * Use float for key pair ttl. Strict test only.
     *
     * @psalm-suppress InvalidScalarArgument
     *
     * @return void
     */
    public function testSetKeyPairTtlInvalidTypeFloat(): void
    {
        $ttl = 76.234;
        $this->expectException(\TypeError::class);
        $this->testStrict->set('foo', 'bar', $ttl);
    }//end testSetKeyPairTtlInvalidTypeFloat()
}//end class

?>