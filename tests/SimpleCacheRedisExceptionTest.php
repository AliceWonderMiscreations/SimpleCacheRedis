<?php
declare(strict_types=1);

/**
 * Unit testing for SimpleCacheRedis Exceptions.
 *
 * @package AWonderPHP/SimpleCacheRedis
 * @author  Alice Wonder <paypal@domblogger.net>
 * @license https://opensource.org/licenses/MIT MIT
 * @link    https://github.com/AliceWonderMiscreations/SimpleCacheRedis
 */

use PHPUnit\Framework\TestCase;

/**
 * Test class for SimpleCacheRedis Exceptions no strict no encryption.
 */
// @codingStandardsIgnoreLine
final class SimpleCacheRedisExceptionTest extends TestCase
{
    /**
     * The test object.
     *
     * @var \AWonderPHP\SimpleCache\SimpleCache
     */
    private $testNotStrict;
    
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
        $this->testNotStrict = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis);
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
     * Feed null data when setting the default TTL.
     *
     * @psalm-suppress NullArgument
     *
     * @return void
     */
    public function testDefaultTtlInvalidTypeNull(): void
    {
        $ttl = null;
        $this->expectException(\TypeError::class);
        $this->testNotStrict->setDefaultSeconds($ttl);
    }//end testDefaultTtlInvalidTypeNull()

    /**
     * Feed boolean data when setting the default TTL.
     *
     * @psalm-suppress PossiblyFalseArgument
     *
     * @return void
     */
    public function testDefaultTtlInvalidTypeBoolean(): void
    {
        $ttl = false;
        $this->expectException(\TypeError::class);
        $this->testNotStrict->setDefaultSeconds($ttl);
    }//end testDefaultTtlInvalidTypeBoolean()

    /**
     * Feed array data when setting the default TTL.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testDefaultTtlInvalidTypeArray(): void
    {
        $ttl = array(1,3,5);
        $this->expectException(\TypeError::class);
        $this->testNotStrict->setDefaultSeconds($ttl);
    }//end testDefaultTtlInvalidTypeArray()

    /**
     * Feed stdClass object data when setting the default TTL.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testDefaultTtlInvalidTypeObject(): void
    {
        $ttl = new \stdClass;
        $ttl->foo = 7;
        $this->expectException(\TypeError::class);
        $this->testNotStrict->setDefaultSeconds($ttl);
    }//end testDefaultTtlInvalidTypeObject()

    /**
     * Use null as key.
     *
     * @psalm-suppress NullArgument
     *
     * @return void
     */
    public function testCacheKeyInvalidTypeNull(): void
    {
        $value = '99 bottles of beer on the wall';
        $key = null;
        $this->expectException(\TypeError::class);
        $this->testNotStrict->set($key, $value);
    }//end testCacheKeyInvalidTypeNull()

    /**
     * Use boolean as key.
     *
     * @psalm-suppress InvalidScalarArgument
     *
     * @return void
     */
    public function testCacheKeyInvalidTypeBoolean(): void
    {
        $value = '99 bottles of beer on the wall';
        $key = true;
        $this->expectException(\TypeError::class);
        $this->testNotStrict->set($key, $value);
    }//end testCacheKeyInvalidTypeBoolean()

    /**
     * Use array as key.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testCacheKeyInvalidTypeArray(): void
    {
        $value = '99 bottles of beer on the wall';
        $key = array(3,4,5);
        $this->expectException(\TypeError::class);
        $this->testNotStrict->set($key, $value);
    }//end testCacheKeyInvalidTypeArray()

    /**
     * Use object as key.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testCacheKeyInvalidTypeObject(): void
    {
        $value = '99 bottles of beer on the wall';
        $key = new \stdClass;
        $key->key = 'foo';
        $this->expectException(\TypeError::class);
        $this->testNotStrict->set($key, $value);
    }//end testCacheKeyInvalidTypeObject()

    /**
     * Use boolean for key pair ttl.
     *
     * @psalm-suppress InvalidScalarArgument
     *
     * @return void
     */
    public function testSetKeyPairTtlInvalidTypeBoolean(): void
    {
        $ttl = true;
        $this->expectException(\TypeError::class);
        $this->testNotStrict->set('foo', 'bar', $ttl);
    }//end testSetKeyPairTtlInvalidTypeBoolean()

    /**
     * Use array for key pair ttl.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testSetKeyPairTtlInvalidTypeArray(): void
    {
        $ttl = array(3,4,5);
        $this->expectException(\TypeError::class);
        $this->testNotStrict->set('foo', 'bar', $ttl);
    }//end testSetKeyPairTtlInvalidTypeArray()

    /**
     * Use object for key pair ttl.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testSetKeyPairTtlInvalidTypeObject(): void
    {
        $ttl = new \stdClass;
        $ttl->foobar = "fubar";
        $this->expectException(\TypeError::class);
        $this->testNotStrict->set('foo', 'bar', $ttl);
    }//end testSetKeyPairTtlInvalidTypeObject()

    /**
     * Set multiple not iterable null.
     *
     * @psalm-suppress NullArgument
     *
     * @return void
     */
    public function testSetMultipleInvalidTypeNull(): void
    {
        $keyValuePairs = null;
        $this->expectException(\TypeError::class);
        $this->testNotStrict->setMultiple($keyValuePairs);
    }//end testSetMultipleInvalidTypeNull()

    /**
     * Set multiple not iterable integer.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testSetMultipleInvalidTypeInteger(): void
    {
        $keyValuePairs = 5;
        $this->expectException(\TypeError::class);
        $this->testNotStrict->setMultiple($keyValuePairs);
    }//end testSetMultipleInvalidTypeInteger()

    /**
     * Set multiple not iterable float.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testSetMultipleInvalidTypeFloat(): void
    {
        $keyValuePairs = 51.50;
        $this->expectException(\TypeError::class);
        $this->testNotStrict->setMultiple($keyValuePairs);
    }//end testSetMultipleInvalidTypeFloat()

    /**
     * Set multiple not iterable boolean.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testSetMultipleInvalidTypeBoolean(): void
    {
        $keyValuePairs = true;
        $this->expectException(\TypeError::class);
        $this->testNotStrict->setMultiple($keyValuePairs);
    }//end testSetMultipleInvalidTypeBoolean()

    /**
     * Set multiple not iterable string.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testSetMultipleInvalidTypeString(): void
    {
        $keyValuePairs = 'This is a string';
        $this->expectException(\TypeError::class);
        $this->testNotStrict->setMultiple($keyValuePairs);
    }//end testSetMultipleInvalidTypeString()

    /**
     * Set multiple with iterable but key not string.
     *
     * @return void
     */
    public function testSetMultipleInvalidTypeKeyWithinNotString(): void
    {
        $obj = new \stdClass;
        $obj->animal = "Frog";
        $obj->mineral = "Quartz";
        $obj->vegetable = "Spinach";
        $keyValuePairs = array(
            "testInt" => 5,
            "testFloat" => 3.278,
            "testString" => "WooHoo",
            "testBoolean" => true,
            "testNull" => null,
            "testArray" => array(1, 2, 3, 4, 5),
            "testObject" => $obj
        );
        // this is what triggers it
        $keyValuePairs[] = 'Hello';
        $this->expectException(\TypeError::class);
        $this->testNotStrict->setMultiple($keyValuePairs);
    }//end testSetMultipleInvalidTypeKeyWithinNotString()

    /**
     * Get multiple not iterable null.
     *
     * @psalm-suppress NullArgument
     *
     * @return void
     */
    public function testGetMultipleInvalidTypeNull(): void
    {
        $keyValuePairs = null;
        $this->expectException(\TypeError::class);
        $this->testNotStrict->getMultiple($keyValuePairs);
    }//end testGetMultipleInvalidTypeNull()

    /**
     * Get multiple not iterable integer.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testGetMultipleInvalidTypeInteger(): void
    {
        $keyValuePairs = 978;
        $this->expectException(\TypeError::class);
        $this->testNotStrict->getMultiple($keyValuePairs);
    }//end testGetMultipleInvalidTypeInteger()

    /**
     * Get multiple not iterable float.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testGetMultipleInvalidTypeFloat(): void
    {
        $keyValuePairs = 97.8;
        $this->expectException(\TypeError::class);
        $this->testNotStrict->getMultiple($keyValuePairs);
    }//end testGetMultipleInvalidTypeFloat()

    /**
     * Get multiple not iterable boolean.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testGetMultipleInvalidTypeBoolean(): void
    {
        $keyValuePairs = true;
        $this->expectException(\TypeError::class);
        $this->testNotStrict->getMultiple($keyValuePairs);
    }//end testGetMultipleInvalidTypeBoolean()

    /**
     * Get multiple not iterable string.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testGetMultipleInvalidTypeString(): void
    {
        $keyValuePairs = "I like to party sometimes until four";
        $this->expectException(\TypeError::class);
        $this->testNotStrict->getMultiple($keyValuePairs);
    }//end testGetMultipleInvalidTypeString()

    /**
     * Get multiple with iterable but key not string.
     *
     * @return void
     */
    public function testGetMultipleInvalidTypeKeyWithinNotString(): void
    {
        $obj = new \stdClass;
        $obj->animal = "Frog";
        $obj->mineral = "Quartz";
        $obj->vegetable = "Spinach";
        $keyValuePairs = array(
            "testInt" => 5,
            "testFloat" => 3.278,
            "testString" => "WooHoo",
            "testBoolean" => true,
            "testNull" => null,
            "testArray" => array(1, 2, 3, 4, 5),
            "testObject" => $obj
        );
        // this is what triggers it
        $keyValuePairs[] = 'Hello';
        $this->expectException(\TypeError::class);
        $this->testNotStrict->getMultiple($keyValuePairs);
    }//end testGetMultipleInvalidTypeKeyWithinNotString()

    /* Invalid Argument Tests */

    /**
     * Try setting a negative default ttl integer.
     *
     * @return void
     */
    public function testDefaultTtlInvalidArgumentNegativeInteger(): void
    {
        $ttl = -7;
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->setDefaultSeconds($ttl);
    }//end testDefaultTtlInvalidArgumentNegativeInteger()

    /**
     * Try setting a negative default ttl DateInterval.
     *
     * @return void
     */
    public function testDefaultTtlInvalidArgumentNegativeDateInterval(): void
    {
        $Today = new \DateTime('2012-01-02');
        $YesterDay = new \DateTime('2012-01-01');
        $interval = $Today->diff($YesterDay);
        $interval = $YesterDay->diff($Today);
        $interval->d = "-1";
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->setDefaultSeconds($interval);
    }//end testDefaultTtlInvalidArgumentNegativeDateInterval()

    /**
     * Try setting empty key.
     *
     * @return void
     */
    public function testSetKeyPairInvalidArgumentEmptyKey(): void
    {
        $key = '    ';
        $value = 'Test Value';
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->set($key, $value);
    }//end testSetKeyPairInvalidArgumentEmptyKey()

    /**
     * Try setting barely too long key.
     *
     * @return void
     */
    public function testSetKeyPairInvalidArgumentKeyBarelyTooLong(): void
    {
        $value = 'Test Value';
        $a='AAAAABB';
        $b='BBBBBBBB';
        $key = 'z';
        for ($i=0; $i<=30; $i++) {
            $key .= $b;
        }
        $key .= $a;
        $keylength = strlen($key);
        $this->assertEquals(256, $keylength);
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->set($key, $value);
    }//end testSetKeyPairInvalidArgumentKeyBarelyTooLong()

    /**
     * Reserved Character In Key Left Curly.
     *
     * @return void
     */
    public function testSetKeyPairInvalidArgumentKeyContainsLeftCurly(): void
    {
        $key = 'key{key';
        $value = 'Test Value';
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->set($key, $value);
    }//end testSetKeyPairInvalidArgumentKeyContainsLeftCurly()

    /**
     * Reserved Character In Key Right Curly.
     *
     * @return void
     */
    public function testSetKeyPairInvalidArgumentKeyContainsRightCurly(): void
    {
        $key = 'key}key';
        $value = 'Test Value';
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->set($key, $value);
    }//end testSetKeyPairInvalidArgumentKeyContainsRightCurly()

    /**
     * Reserved Character In Key Left Parenthesis.
     *
     * @return void
     */
    public function testSetKeyPairInvalidArgumentKeyContainsLeftParenthesis(): void
    {
        $key = 'key(key';
        $value = 'Test Value';
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->set($key, $value);
    }//end testSetKeyPairInvalidArgumentKeyContainsLeftParenthesis()

    /**
     * Reserved Character In Key Right Parenthesis.
     *
     * @return void
     */
    public function testSetKeyPairInvalidArgumentKeyContainsRightParenthesis(): void
    {
        $key = 'key)key';
        $value = 'Test Value';
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->set($key, $value);
    }//end testSetKeyPairInvalidArgumentKeyContainsRightParenthesis()

    /**
     * Reserved Character In Key Forward Slash.
     *
     * @return void
     */
    public function testSetKeyPairInvalidArgumentKeyContainsForwardSlash(): void
    {
        $key = 'key/key';
        $value = 'Test Value';
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->set($key, $value);
    }//end testSetKeyPairInvalidArgumentKeyContainsForwardSlash()

    /**
     * Reserved Character In Key Back Slash.
     *
     * @return void
     */
    public function testSetKeyPairInvalidArgumentKeyContainsBackSlash(): void
    {
        $key = 'key\key';
        $value = 'Test Value';
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->set($key, $value);
    }//end testSetKeyPairInvalidArgumentKeyContainsBackSlash()

    /**
     * Reserved Character In Key atmark.
     *
     * @return void
     */
    public function testSetKeyPairInvalidArgumentKeyContainsAtmark(): void
    {
        $key = 'key@key';
        $value = 'Test Value';
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->set($key, $value);
    }//end testSetKeyPairInvalidArgumentKeyContainsAtmark()

    /**
     * Reserved Character In Key colon.
     *
     * @return void
     */
    public function testSetKeyPairInvalidArgumentKeyContainsColon(): void
    {
        $key = 'key:key';
        $value = 'Test Value';
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->set($key, $value);
    }//end testSetKeyPairInvalidArgumentKeyContainsColon()

    /**
     * Negative TTL in set integer.
     *
     * @return void
     */
    public function testSetKeyPairTtlInvalidArgumentNegativeInteger(): void
    {
        $key = "foo";
        $value = "bar";
        $ttl = -379;
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->set($key, $value, $ttl);
    }//end testSetKeyPairTtlInvalidArgumentNegativeInteger()

    /**
     * Negative TTL Date String In Past.
     *
     * @return void
     */
    public function testSetKeyPairTtlInvalidArgumentDateStringInPast(): void
    {
        $key = "foo";
        $value = "bar";
        $ttl = "1984-02-21";
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->set($key, $value, $ttl);
    }//end testSetKeyPairTtlInvalidArgumentDateStringInPast()

    /**
     * Negative TTL Date Range In Past.
     *
     * @return void
     */
    public function testSetKeyPairTtlInvalidArgumentDateRangeInPast(): void
    {
        $key = "foo";
        $value = "bar";
        $ttl = "-1 week";
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->set($key, $value, $ttl);
    }//end testSetKeyPairTtlInvalidArgumentDateRangeInPast()

    /**
     * Negative TTL Date Interval In Past.
     *
     * @return void
     */
    public function testSetKeyPairTtlInvalidArgumentNegativeDateInterval(): void
    {
        $key = "foo";
        $value = "bar";
        $Today = new \DateTime('2012-01-02');
        $YesterDay = new \DateTime('2012-01-01');
        $interval = $Today->diff($YesterDay);
        $interval = $YesterDay->diff($Today);
        $interval->d = "-1";
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->set($key, $value, $interval);
    }//end testSetKeyPairTtlInvalidArgumentNegativeDateInterval()

    /**
     * Bogus String TTL.
     *
     * @return void
     */
    public function testSetKeyPairTtlInvalidArgumentBogusString(): void
    {
        $key = "foo";
        $value = "bar";
        $ttl = "LKvfs4dh#";
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->set($key, $value, $ttl);
    }//end testSetKeyPairTtlInvalidArgumentBogusString()

    /**
     * Test key in iterable not legal.
     *
     * @return void
     */
    public function testSetMultipleInvalidArgumentKeyInIterableHasReservedCharacter(): void
    {
        $arr = array(
            'key1' => 'value1',
            'key2' => 'value2',
            'ke}y3' => 'value3',
            'key4' => 'value4',
            'key5' => 'value5'
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->testNotStrict->setMultiple($arr);
    }//end testSetMultipleInvalidArgumentKeyInIterableHasReservedCharacter()
}//end class

?>