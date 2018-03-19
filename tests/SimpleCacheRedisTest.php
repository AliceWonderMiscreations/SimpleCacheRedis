<?php
declare(strict_types=1);

/**
 * Unit testing for SimpleCacheRedis.
 *
 * @package AWonderPHP/SimpleCacheRedis
 * @author  Alice Wonder <paypal@domblogger.net>
 * @license https://opensource.org/licenses/MIT MIT
 * @link    https://github.com/AliceWonderMiscreations/SimpleCacheRedis
 */

use PHPUnit\Framework\TestCase;

/**
 * Test class for SimpleCacheRedis no strict no encryption.
 */
// @codingStandardsIgnoreLine
final class SimpleCacheRedisTest extends TestCase
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

    /**
     * Cache test miss should return null, not false.
     *
     * @return void
     */
    public function testMissReturnsNull(): void
    {
        $key = 'I do not exist';
        $a = $this->testNotStrict->get($key);
        $this->assertNull($a);
    }//end testMissReturnsNull()

    /**
     * Set and retrieve a string.
     *
     * @return void
     */
    public function testSetAndRetrieveString(): void
    {
        $key = "A test key";
        $expected = "Fubar String";
        $this->testNotStrict->set($key, $expected);
        $actual = $this->testNotStrict->get($key);
        $this->assertEquals($expected, $actual);
    }//end testSetAndRetrieveString()

    /**
     * Set and retrieve an integer.
     *
     * @return void
     */
    public function testSetAndRetrieveInteger(): void
    {
        $key = "A test key";
        $expected = 27;
        $this->testNotStrict->set($key, $expected);
        $actual = $this->testNotStrict->get($key);
        $this->assertEquals($expected, $actual);
    }//end testSetAndRetrieveInteger()

    /**
     * Set and retrieve a float.
     *
     * @return void
     */
    public function testSetAndRetrieveFloat(): void
    {
        $key = "A test key";
        $expected = 7.234;
        $this->testNotStrict->set($key, $expected);
        $actual = $this->testNotStrict->get($key);
        $this->assertEquals($expected, $actual);
    }//end testSetAndRetrieveFloat()

    /**
     * Set and retrieve a Boolean, test both true and false.
     *
     * @return void
     */
    public function testSetAndRetrieveBoolean(): void
    {
        $key = "A test key";
        $this->testNotStrict->set($key, true);
        $actual = $this->testNotStrict->get($key);
        $this->assertTrue($actual);
        $this->testNotStrict->set($key, false);
        $actual = $this->testNotStrict->get($key);
        $this->assertFalse($actual);
    }//end testSetAndRetrieveBoolean()

    /**
     * Set and retrieve a null.
     *
     * @return void
     */
    public function testSetAndRetrieveNull(): void
    {
        $key = "A nothing test";
        $this->testNotStrict->set($key, null);
        $a = $this->testNotStrict->get($key);
        $this->assertNull($a);
        $bool = $this->testNotStrict->has($key);
        $this->assertTrue($bool);
    }//end testSetAndRetrieveNull()

    /**
     * Set and retrieve an array.
     *
     * @return void
     */
    public function testSetAndRetrieveArray(): void
    {
        $obj = new \stdClass;
        $obj->animal = "Frog";
        $obj->mineral = "Quartz";
        $obj->vegetable = "Spinach";
        $arr = array(
            "testInt" => 5,
            "testFloat" => 3.278,
            "testString" => "WooHoo",
            "testBoolean" => true,
            "testNull" => null,
            "testArray" => array(1, 2, 3, 4, 5),
            "testObject" => $obj
        );
        $key = "TestArray";
        $this->testNotStrict->set($key, $arr);
        $a = $this->testNotStrict->get($key);
        $bool = is_array($a);
        $this->assertTrue($bool);
        $this->assertEquals($arr['testInt'], $a['testInt']);
        $this->assertEquals($arr['testFloat'], $a['testFloat']);
        $this->assertEquals($arr['testString'], $a['testString']);
        $this->assertEquals($arr['testBoolean'], $a['testBoolean']);
        $this->assertNull($a['testNull']);
        $this->assertEquals($arr['testArray'], $a['testArray']);
        $this->assertEquals($arr['testObject'], $a['testObject']);
    }//end testSetAndRetrieveArray()

    /**
     * Set and retrieve a n object.
     *
     * @return void
     */
    public function testSetAndRetrieveObject(): void
    {
        $obj = new \stdClass;
        $obj->animal = "Frog";
        $obj->mineral = "Quartz";
        $obj->vegetable = "Spinach";
        $testObj = new \stdClass;
        $testObj->testInt = 5;
        $testObj->testFloat = 3.278;
        $testObj->testString = "WooHoo";
        $testObj->testBoolean = true;
        $testObj->testNull = null;
        $testObj->testArray = array(1,2,3,4,5);
        $testObj->testObject = $obj;
        $key = "TestObject";
        $this->testNotStrict->set($key, $testObj);
        $a = $this->testNotStrict->get($key);
        $bool = is_object($a);
        $this->assertTrue($bool);
        $this->assertEquals($testObj->testInt, $a->testInt);
        $this->assertEquals($testObj->testFloat, $a->testFloat);
        $this->assertEquals($testObj->testString, $a->testString);
        $this->assertEquals($testObj->testBoolean, $a->testBoolean);
        $this->assertNull($a->testNull);
        $this->assertEquals($testObj->testArray, $a->testArray);
        $this->assertEquals($testObj->testObject, $a->testObject);
    }//end testSetAndRetrieveObject()

    /**
     * Delete a key.
     *
     * @return void
     */
    public function testDeleteAKey(): void
    {
        $this->testNotStrict->set('Test Key 1', 'foo 1');
        $this->testNotStrict->set('Test Key 2', 'foo 2');
        $this->testNotStrict->set('Test Key 3', 'foo 3');
        $bool = $this->testNotStrict->has('Test Key 1');
        $this->assertTrue($bool);
        $bool = $this->testNotStrict->has('Test Key 2');
        $this->assertTrue($bool);
        $bool = $this->testNotStrict->has('Test Key 3');
        $this->assertTrue($bool);
        $this->testNotStrict->delete('Test Key 2');
        $bool = $this->testNotStrict->has('Test Key 1');
        $this->assertTrue($bool);
        $bool = $this->testNotStrict->has('Test Key 2');
        $this->assertFalse($bool);
        $bool = $this->testNotStrict->has('Test Key 3');
        $this->assertTrue($bool);
    }//end testDeleteAKey()

    /**
     * Test Key Length of 1 character.
     *
     * @return void
     */
    public function testAcceptKeyLengthOf1(): void
    {
        $key = 'j';
        $expected = 'fooBar 2000';
        $this->testNotStrict->set($key, $expected);
        $actual = $this->testNotStrict->get($key);
        $this->assertEquals($expected, $actual);
    }//end testAcceptKeyLengthOf1()

    /**
     * Test Key Length of 255 characters.
     *
     * @return void
     */
    public function testAcceptKeyLengthOf255(): void
    {
        $a='AAAAABB';
        $b='BBBBBBBB';
        $key = '';
        for ($i=0; $i<=30; $i++) {
            $key .= $b;
        }
        $key .= $a;
        $keylength = strlen($key);
        $this->assertEquals(255, $keylength);
        $expected = 'fooBar 2001';
        $this->testNotStrict->set($key, $expected);
        $actual = $this->testNotStrict->get($key);
        $this->assertEquals($expected, $actual);
    }//end testAcceptKeyLengthOf255()

    /**
     * Accept multibyte character key.
     *
     * @return void
     */
    public function testAcceptMultibyteCharacterKey(): void
    {
        $key = 'いい知らせ';
        $expected = 'חדשות טובות';
        $this->testNotStrict->set($key, $expected);
        $actual = $this->testNotStrict->get($key);
        $this->assertEquals($expected, $actual);
    }//end testAcceptMultibyteCharacterKey()

    /**
     * Set ttl for key => value pair as integer.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testSetCacheLifeAsInteger(): void
    {
        $key = 'Cache Life As Integer';
        $value = 'Some Value';
        $seconds = 27;
        $this->testNotStrict->set($key, $value, $seconds);
        $realKey = $this->testNotStrict->getRealKey($key);
        $cacheTTL = $this->redis->ttl($realKey);
        $this->assertEquals($seconds, $cacheTTL);
    }//end testSetCacheLifeAsInteger()

    /**
     * Set ttl for key => value with unix timestamp.
     *
     * @psalm-suppress InvalidArgument
     * @psalm-suppress InvalidOperand
     *
     * @return void
     */
    public function testSetCacheLifeAsUnixTimestamp(): void
    {
        $key = 'Cache Life As TS';
        $value = 'Some Value';
        $rnd = rand(34, 99);
        $ttl = time() + $rnd;
        $this->testNotStrict->set($key, $value, $ttl);
        $realKey = $this->testNotStrict->getRealKey($key);
        $cacheTTL = $this->redis->ttl($realKey);
        $race = abs($cacheTTL - $rnd);
        $this->assertLessThan(3, $race);
    }//end testSetCacheLifeAsUnixTimestamp()

    /**
     * Set ttl for key => value with date range.
     *
     * @psalm-suppress InvalidArgument
     * @psalm-suppress InvalidOperand
     *
     * @return void
     */
    public function testSetCacheLifeAsStringWithDateRange(): void
    {
        $key = "Cache Life as string with date range";
        $value = "Staying Alive";
        $ttl = '+1 week';
        $this->testNotStrict->set($key, $value, $ttl);
        $realKey = $this->testNotStrict->getRealKey($key);
        $cacheTTL = $this->redis->ttl($realKey);
        $race = abs($cacheTTL - 604800);
        $this->assertLessThan(3, $race);
    }//end testSetCacheLifeAsStringWithDateRange()

    /**
     * Set TTL for key => value with date as string.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testSetCacheLifeAsStringWithFixedDate(): void
    {
        $key = "Cache Life as string with date";
        $value = "Staying More Alive";
        $a = (24 * 60 * 60);
        $b = (48 * 60 * 60);
        $dateUnix = time() + $b;
        $dateString = date('Y-m-d', $dateUnix);
        $this->testNotStrict->set($key, $value, $dateString);
        $realKey = $this->testNotStrict->getRealKey($key);
        $cacheTTL = $this->redis->ttl($realKey);
        $a--;
        $b++;
        $this->assertLessThan($cacheTTL, $a);
        $this->assertLessThan($b, $cacheTTL);
    }//end testSetCacheLifeAsStringWithFixedDate()

    /**
     * Test with a very very very large TTL but not current timestamp large.
     *
     * @psalm-suppress InvalidArgument
     * @psalm-suppress InvalidOperand
     *
     * @return void
     */
    public function testSetCacheLifeAsVeryVeryLargeInteger(): void
    {
        $key = "Cache Life As Huge Integer";
        $value = "Size Matters";
        $ttl = time() - 7;
        $this->testNotStrict->set($key, $value, $ttl);
        $realKey = $this->testNotStrict->getRealKey($key);
        $cacheTTL = $this->redis->ttl($realKey);
        $race = abs($cacheTTL - $ttl);
        $this->assertLessThan(3, $race);
    }//end testSetCacheLifeAsVeryVeryLargeInteger()

    /**
     * Test setting $key => $value pair as a DateInterval object.
     *
     * @psalm-suppress InvalidArgument
     * @psalm-suppress InvalidOperand
     *
     * @return void
     */
    public function testSetCacheLifeAsDateIntervalObject(): void
    {
        $key = "Cache Life As Date Interval";
        $value = "yum";
        $ttl = new \DateInterval('P3DT4H');
        $expected = 273600;
        $this->testNotStrict->set($key, $value, $ttl);
        $realKey = $this->testNotStrict->getRealKey($key);
        $cacheTTL = $this->redis->ttl($realKey);
        $race = abs($cacheTTL - $expected);
        $this->assertLessThan(3, $race);
    }//end testSetCacheLifeAsDateIntervalObject()

    /**
     * Set default TTL with integer.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testSetDefaultSecondsWithInteger(): void
    {
        $expected = 5;
        $this->testNotStrict->setDefaultSeconds($expected);
        $key = 'Setting Default';
        $value = 'Pie in the Sky';
        $this->testNotStrict->set($key, $value);
        $realKey = $this->testNotStrict->getRealKey($key);
        $cacheTTL = $this->redis->ttl($realKey);
        $this->assertEquals($expected, $cacheTTL);
    }//end testSetDefaultSecondsWithInteger()

    /**
     * Set default TTL with DateInterval.
     *
     * @psalm-suppress InvalidArgument
     * @psalm-suppress InvalidOperand
     *
     * @return void
     */
    public function testDefaultSecondsWithDateInterval(): void
    {
        $interval = new \DateInterval('P3DT4H');
        $this->testNotStrict->setDefaultSeconds($interval);
        $expected = 273600;
        $key = 'Setting Default';
        $value = 'Pie in the Sky';
        $this->testNotStrict->set($key, $value);
        $realKey = $this->testNotStrict->getRealKey($key);
        $cacheTTL = $this->redis->ttl($realKey);
        $race = abs($cacheTTL - $expected);
        $this->assertLessThan(3, $race);
    }//end testDefaultSecondsWithDateInterval()

    /**
     * Set multiple key => value pairs.
     *
     * @return void
     */
    public function testSetMultipleKeyValuePairsAtOnce(): void
    {
        $obj = new \stdClass;
        $obj->animal = "Frog";
        $obj->mineral = "Quartz";
        $obj->vegetable = "Spinach";
        $arr = array(
            "testInt" => 5,
            "testFloat" => 3.278,
            "testString" => "WooHoo",
            "testBoolean" => true,
            "testNull" => null,
            "testArray" => array(1, 2, 3, 4, 5),
            "testObject" => $obj
        );
        $arr['Hello'] = null;
        $arr['Goodbye'] = null;
        $this->testNotStrict->setMultiple($arr);
        foreach (array(
            'testInt',
            'testFloat',
            'testString',
            'testBoolean',
            'testArray',
            'testObject'
        ) as $key) {
            $a = $this->testNotStrict->get($key);
            switch ($key) {
                case 'testObject':
                    $this->assertEquals($a->animal, $obj->animal);
                    $this->assertEquals($a->mineral, $obj->mineral);
                    $this->assertEquals($a->vegetable, $obj->vegetable);
                    break;
                default:
                    $this->assertEquals($arr[$key], $a);
            }
        }
        // test the three that should be null
        foreach (array('testNull', 'Hello', 'Goodbye') as $key) {
            $a = $this->testNotStrict->get($key);
            $this->assertNull($a);
            $bool = $this->testNotStrict->has($key);
            $this->assertTrue($bool);
        }
    }//end testSetMultipleKeyValuePairsAtOnce()

    /**
     * Test Get Multiple Pairs At Once.
     *
     * @return void
     */
    public function testGetMultipleKeyValuePairsAtOnce(): void
    {
        $obj = new \stdClass;
        $obj->animal = "Frog";
        $obj->mineral = "Quartz";
        $obj->vegetable = "Spinach";
        $arr = array(
            "testInt" => 5,
            "testFloat" => 3.278,
            "testString" => "WooHoo",
            "testBoolean" => true,
            "testNull" => null,
            "testArray" => array(1, 2, 3, 4, 5),
            "testObject" => $obj
        );
        $arr['Hello'] = null;
        $arr['Goodbye'] = null;
        $this->testNotStrict->setMultiple($arr);
        $tarr = array();
        $tarr[] = 'testBoolean';
        $tarr[] = 'testFloat';
        $tarr[] = 'testCacheMiss';
        $tarr[] = 'testString';
        $result = $this->testNotStrict->getMultiple($tarr);
        $boolean = array_key_exists('testBoolean', $result);
        $this->assertTrue($boolean);
        $boolean = array_key_exists('testFloat', $result);
        $this->assertTrue($boolean);
        $boolean = array_key_exists('testCacheMiss', $result);
        $this->assertTrue($boolean);
        $boolean = array_key_exists('testString', $result);
        $this->assertTrue($boolean);
        $this->assertEquals($result['testBoolean'], $arr['testBoolean']);
        $this->assertEquals($result['testFloat'], $arr['testFloat']);
        $this->assertEquals($result['testString'], $arr['testString']);
        $this->assertNull($result['testCacheMiss']);
    }//end testGetMultipleKeyValuePairsAtOnce()

    /**
     * Test deleting multiple keys at once.
     *
     * @return void
     */
    public function testDeleteMultipleKeyValuePairsAtOnce(): void
    {
        $arr = array();
        $records = rand(220, 370);
        for ($i=0; $i <= $records; $i++) {
            $key = 'KeyNumber-' . $i;
            $val = 'ValueNumber-' . $i;
            $arr[$key] = $val;
        }
        $start = count($arr);
        $this->testNotStrict->setMultiple($arr);

        $del = array();
        $n = rand(75, 167);
        $max = $records - 5;
        for ($i=0; $i<$n; $i++) {
            $key = 'KeyNumber-' . rand(5, $max);
            if (! in_array($key, $del)) {
                $del[] = $key;
            }
        }
        $delcount = count($del);
        $expected = $start - $delcount;
        $this->testNotStrict->deleteMultiple($del);
        $hits = 0;
        for ($i=0; $i<= $records; $i++) {
            $key = 'KeyNumber-' . $i;
            if ($this->testNotStrict->has($key)) {
                $hits++;
            }
        }
        $this->assertEquals($expected, $hits);
    }//end testDeleteMultipleKeyValuePairsAtOnce()
}//end class

?>