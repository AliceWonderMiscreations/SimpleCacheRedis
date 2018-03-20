<?php
declare(strict_types=1);

/**
 * Unit testing that involves constructor parameters.
 *
 * @package AWonderPHP/SimpleCacheRedis
 * @author  Alice Wonder <paypal@domblogger.net>
 * @license https://opensource.org/licenses/MIT MIT
 * @link    https://github.com/AliceWonderMiscreations/SimpleCacheRedis
 */

use PHPUnit\Framework\TestCase;

/**
 * Test class for SimpleCache no strict no encryption
 */
// @codingStandardsIgnoreLine
final class SimpleCacheRedisConstructorTest extends TestCase
{
    /**
     * The directory with JSON config test data.
     *
     * @var string
     */
    protected $tdir;
    
    /**
     * The redis object
     *
     * @var \Redis
     */
    private $redis;

    /**
     * Setup function to set test data directory.
     *
     * @return void
     */
    public function setUp()
    {
        $this->tdir = dirname(__FILE__);
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
        $this->redis->flushDB();
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
     * Check to see if three character prefix works.
     *
     * @return void
     */
    public function testConstructorUsingThreeCharacterPrefix(): void
    {
        $prefix = 'AAA';
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix);
        $key = 'Some Key';
        $value = 'Some Value';
        $simpleCache->set($key, $value);
        $realKey = $simpleCache->getRealKey($key);
        $setPrefix = substr($realKey, 0, 4);
        $prefix .= '_';
        $this->assertEquals($prefix, $setPrefix);
    }//end testConstructorUsingThreeCharacterPrefix()

    /**
     * Check to see if three character prefix works.
     *
     * @return void
     */
    public function testConstructorUsingThirtyTwoCharacterPrefix(): void
    {
        $prefix = 'AAAAAAAABBBBBBBBCCCCCCCCDDDDDDDD';
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix);
        $key = 'Some Key';
        $value = 'Some Value';
        $simpleCache->set($key, $value);
        $realKey = $simpleCache->getRealKey($key);
        $setPrefix = substr($realKey, 0, 33);
        $prefix .= '_';
        $this->assertEquals($prefix, $setPrefix);
    }//end testConstructorUsingThirtyTwoCharacterPrefix()

    /**
     * Check for failure on empty prefix.
     *
     * @return void
     */
    public function testConstructorInvalidArgumentEmptyPrefix(): void
    {
        $prefix = '   ';
        $this->expectException(\InvalidArgumentException::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix);
    }//end testConstructorInvalidArgumentEmptyPrefix()

    /**
     * Check for failure on two letter prefix.
     *
     * @return void
     */
    public function testConstructorInvalidArgumentTwoLetterPrefix(): void
    {
        $prefix = 'AA';
        $this->expectException(\InvalidArgumentException::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix);
    }//end testConstructorInvalidArgumentTwoLetterPrefix()

    /**
     * Check for failure on non-alphanumeric prefix.
     *
     * @return void
     */
    public function testConstructorInvalidArgumentNonAlphaNumericPrefix(): void
    {
        $prefix = '  aa_bb ';
        $this->expectException(\InvalidArgumentException::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix);
    }//end testConstructorInvalidArgumentNonAlphaNumericPrefix()

    /**
     * Check for failure on 33 letter prefix.
     *
     * @return void
     */
    public function testConstructorInvalidArgument33LetterPrefix(): void
    {
        $prefix = 'AAAAAAAABBBBBBBBCCCCCCCCDDDDDDDDE';
        $this->expectException(\InvalidArgumentException::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix);
    }//end testConstructorInvalidArgument33LetterPrefix()

    /**
     * Check for failure on boolean prefix.
     *
     * @psalm-suppress InvalidScalarArgument
     *
     * @return void
     */
    public function testConstructorInvalidTypePrefixBoolean(): void
    {
        $prefix = true;
        $this->expectException(\TypeError::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix);
    }//end testConstructorInvalidTypePrefixBoolean()

    /**
     * Check for failure on array prefix.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testConstructorInvalidTypePrefixArray(): void
    {
        $prefix = array(1,2,3,4,5);
        $this->expectException(\TypeError::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix);
    }//end testConstructorInvalidTypePrefixArray()

    /**
     * Check for failure on object prefix.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testConstructorInvalidTypePrefixObject(): void
    {
        $prefix = new \stdClass;
        $prefix->foobar = "fubar";
        $this->expectException(\TypeError::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix);
    }//end testConstructorInvalidTypePrefixObject()

    /**
     * Check for strict failure on integer prefix.
     *
     * @psalm-suppress InvalidScalarArgument
     *
     * @return void
     */
    public function testConstructorStrictInvalidTypePrefixInteger(): void
    {
        $prefix = new \stdClass;
        $prefix = 5555;
        $this->expectException(\TypeError::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix, null, true);
    }//end testConstructorStrictInvalidTypePrefixInteger()

    /**
     * Check for strict failure on float prefix.
     *
     * @psalm-suppress InvalidScalarArgument
     *
     * @return void
     */
    public function testConstructorStrictInvalidTypePrefixFloat(): void
    {
        $prefix = new \stdClass;
        $prefix = 55.55;
        $this->expectException(\TypeError::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix, null, true);
    }//end testConstructorStrictInvalidTypePrefixFloat()

    /**
     * Check to see if eight character salt works.
     *
     * @return void
     */
    public function testConstructorUsingEightCharacterSalt(): void
    {
        $salt = 'ldr##sdr';
        $simpleCacheA = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, null, $salt);
        $simpleCacheB = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis);
        $key = 'Some Key';
        $value = 'Some Value';
        $simpleCacheA->set($key, $value);
        $simpleCacheB->set($key, $value);
        $a = $simpleCacheA->get($key);
        $b = $simpleCacheB->get($key);
        $this->assertEquals($a, $b);
        $realKeyA = $simpleCacheA->getRealKey($key);
        $realKeyB = $simpleCacheB->getRealKey($key);
        $this->assertNotEquals($realKeyA, $realKeyB);
    }//end testConstructorUsingEightCharacterSalt()

    /**
     * Check to see if really long salt works.
     *
     * @return void
     */
    public function testConstructorUsingAbsurdlyLongSalt(): void
    {
        $presalt = 'zJpn1hit9u%%yn8hN#ODco$$3w8rp}Hv1bsnADDYrmLjeG';
        $salt = '';
        for ($i=0; $i<=200; $i++) {
            $salt .= str_shuffle($presalt);
        }
        $simpleCacheA = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, null, $salt);
        $simpleCacheB = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis);
        $key = 'Some Key';
        $value = 'Some Value';
        $simpleCacheA->set($key, $value);
        $simpleCacheB->set($key, $value);
        $a = $simpleCacheA->get($key);
        $b = $simpleCacheB->get($key);
        $this->assertEquals($a, $b);
        $realKeyA = $simpleCacheA->getRealKey($key);
        $realKeyB = $simpleCacheB->getRealKey($key);
        $this->assertNotEquals($realKeyA, $realKeyB);
    }//end testConstructorUsingAbsurdlyLongSalt()

    /**
     * Check for failure on empty salt.
     *
     * @return void
     */
    public function testConstructorInvalidArgumentEmptySalt(): void
    {
        $salt = '        ';
        $this->expectException(\InvalidArgumentException::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, null, $salt);
    }//end testConstructorInvalidArgumentEmptySalt()

    /**
     * Check for failure on too short salt.
     *
     * @return void
     */
    public function testConstructorInvalidArgumentSevenCharacterSalt(): void
    {
        $salt = '   asdfghj     ';
        $this->expectException(\InvalidArgumentException::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, null, $salt);
    }//end testConstructorInvalidArgumentSevenCharacterSalt()

    /**
     * Check for failure on boolean salt.
     *
     * @psalm-suppress InvalidScalarArgument
     *
     * @return void
     */
    public function testConstructorInvalidTypeSaltBoolean(): void
    {
        $salt = true;
        $this->expectException(\TypeError::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, null, $salt);
    }//end testConstructorInvalidTypeSaltBoolean()

    /**
     * Check for failure on array salt.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testConstructorInvalidTypeSaltArray(): void
    {
        $salt = array(1,2,3,4,5,6,7,8,9);
        $this->expectException(\TypeError::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, null, $salt);
    }//end testConstructorInvalidTypeSaltArray()

    /**
     * Check for failure on object salt.
     *
     * @psalm-suppress InvalidArgument
     *
     * @return void
     */
    public function testConstructorInvalidTypeSaltObject(): void
    {
        $salt = new \stdClass;
        $salt->foobar = "fubar98765";
        $this->expectException(\TypeError::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, null, $salt);
    }//end testConstructorInvalidTypeSaltObject()

    /**
     * Check for strict failure on integer salt.
     *
     * @psalm-suppress InvalidScalarArgument
     *
     * @return void
     */
    public function testConstructorStrictInvalidTypeSaltInteger(): void
    {
        $salt = 123456789876543;
        $this->expectException(\TypeError::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, null, $salt, true);
    }//end testConstructorStrictInvalidTypeSaltInteger()

    /**
     * Check for strict failure on float salt.
     *
     * @psalm-suppress InvalidScalarArgument
     *
     * @return void
     */
    public function testConstructorStrictInvalidTypeSaltFloat(): void
    {
        $salt = 1234567.8;
        $this->expectException(\TypeError::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, null, $salt, true);
    }//end testConstructorStrictInvalidTypeSaltFloat()

    /* sodium related */

    /**
     * Check for failure on null key to sodium.
     *
     * @psalm-suppress NullArgument
     *
     * @return void
     */
    public function testSodiumConstructorTypeErrorNullSecret(): void
    {
        $secret = null;
        $this->expectException(\TypeError::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedisSodium($this->redis, $secret);
    }//end testSodiumConstructorTypeErrorNullSecret()

    /**
     * Check for failure on secret too short binary.
     *
     * @return void
     */
    public function testSodiumConstructorInvalidArgumentSecretTooShortBinary(): void
    {
        $secret = random_bytes(24);
        $this->expectException(\InvalidArgumentException::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedisSodium($this->redis, $secret);
    }//end testSodiumConstructorInvalidArgumentSecretTooShortBinary()

    /**
     * Check for failure on secret too short hex.
     *
     * @return void
     */
    public function testSodiumConstructorInvalidArgumentSecretTooShortHex(): void
    {
        $secret = random_bytes(24);
        $hex = bin2hex($secret);
        $this->expectException(\InvalidArgumentException::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedisSodium($this->redis, $hex);
    }//end testSodiumConstructorInvalidArgumentSecretTooShortHex()

    /**
     * Check for failure on secret too long binary.
     *
     * @return void
     */
    public function testSodiumConstructorInvalidArgumentSecretTooLongBinary(): void
    {
        $secret = random_bytes(40);
        $this->expectException(\InvalidArgumentException::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedisSodium($this->redis, $secret);
    }//end testSodiumConstructorInvalidArgumentSecretTooLongBinary()

    /**
     * Check for failure on secret too long hex.
     *
     * @return void
     */
    public function testSodiumConstructorInvalidArgumentSecretTooLongHex(): void
    {
        $secret = random_bytes(40);
        $hex = bin2hex($secret);
        $this->expectException(\InvalidArgumentException::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedisSodium($this->redis, $hex);
    }//end testSodiumConstructorInvalidArgumentSecretTooLongHex()

    /**
     * Check for failure on printable secret.
     *
     * @return void
     */
    public function testSodiumConstructorInvalidArgumentSecretAllPrintableCharacters(): void
    {
        //this is a 32 byte string, do we catch it?
        $secret = 'Crimson and clover over and over';
        $this->expectException(\InvalidArgumentException::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedisSodium($this->redis, $secret);
    }//end testSodiumConstructorInvalidArgumentSecretAllPrintableCharacters()

    /* configuration file */

    /**
     * Check that we can load from valid config file.
     *
     * @return void
     */
    public function testSodiumFromValidConfigFile(): void
    {
        $json = $this->tdir . '/valid.json';
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedisSodium($this->redis, $json);
        $key = 'test key';
        $value = 'test value';
        $simpleCache->set($key, $value);
        $a = $simpleCache->get($key);
        $this->assertEquals($a, $value);
    }//end testSodiumFromValidConfigFile()

    /**
     * Check for error bad config file.
     *
     * @return void
     */
    public function testSodiumErrorExceptionBadConfigFile(): void
    {
        $json = $this->tdir . '/badjson.json';
        $this->expectException(\ErrorException::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedisSodium($this->redis, $json);
    }//end testSodiumErrorExceptionBadConfigFile()

    /**
     * Check for error config file no secret.
     *
     * @return void
     */
    public function testSodiumTypeErrorConfigFileWithoutSecret(): void
    {
        $json = $this->tdir . '/nosecret.json';
        $this->expectException(\TypeError::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedisSodium($this->redis, $json);
    }//end testSodiumTypeErrorConfigFileWithoutSecret()

    /**
     * Check for error config file secret too short.
     *
     * @return void
     */
    public function testSodiumInvalidArgumentConfigFileSecretTooShort(): void
    {
        $json = $this->tdir . '/shortkey.json';
        $this->expectException(\InvalidArgumentException::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedisSodium($this->redis, $json);
    }//end testSodiumInvalidArgumentConfigFileSecretTooShort()

    /**
     * Check for error config file secret too long.
     *
     * @return void
     */
    public function testSodiumInvalidArgumentConfigFileSecretTooLong(): void
    {
        $json = $this->tdir . '/longkey.json';
        $this->expectException(\InvalidArgumentException::class);
        $simpleCache = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedisSodium($this->redis, $json);
    }//end testSodiumInvalidArgumentConfigFileSecretTooLong()

    /**
     * Test to make sure we can clear the local cache without clearing other cache.
     *
     * @return void
     */
    public function testCacheClearLocalPrefixOnly(): void
    {
        $prefix = 'LOLIAMUNIQUE';
        $simpleCacheA = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix);
        $prefix = 'IAMUNIQUE';
        $simpleCacheB = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix);
        $simpleCacheA->set('KeyShouldSurvive', 'Staying Alive');
        $simpleCacheB->set('key1', 'value1');
        $simpleCacheB->set('key2', 'value2');
        $simpleCacheB->set('key3', 'value3');
        $simpleCacheB->set('key4', 'value4');
        $simpleCacheB->set('key5', 'value5');
        $simpleCacheA->set('KeyShouldAlsoSurvive', 'Ooh ooh ooh ooh');
        // there should be seven total entries
        $count = $this->redis->dbSize();
        $this->assertEquals(7, $count);
        $simpleCacheB->clear();
        // now there should be two
        $count = $this->redis->dbSize();
        $this->assertEquals(2, $count);
        $value = $simpleCacheA->get('KeyShouldSurvive');
        $this->assertEquals('Staying Alive', $value);
        $value = $simpleCacheA->get('KeyShouldAlsoSurvive');
        $this->assertEquals('Ooh ooh ooh ooh', $value);
    }//end testCacheClearLocalPrefixOnly()

    /**
     * Test to make sure we can clear everything.
     *
     * @return void
     */
    public function testCacheClearEverything(): void
    {
        $prefix = 'PREFIXA';
        $simpleCacheA = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix);
        $prefix = 'PREFIXB';
        $simpleCacheB = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix);
        $prefix = 'PREFIXC';
        $simpleCacheC = new \AWonderPHP\SimpleCacheRedis\SimpleCacheRedis($this->redis, $prefix);
        $simpleCacheA->set('key1', 'value1');
        $simpleCacheA->set('key2', 'value2');
        $simpleCacheA->set('key3', 'value3');
        $simpleCacheA->set('key4', 'value4');
        $simpleCacheA->set('key5', 'value5');
        $simpleCacheB->set('key6', 'value6');
        $simpleCacheB->set('key7', 'value7');
        $simpleCacheB->set('key8', 'value8');
        $simpleCacheB->set('key9', 'value9');
        $simpleCacheB->set('key10', 'value10');
        $simpleCacheC->set('key11', 'value11');
        $simpleCacheC->set('key12', 'value12');
        $simpleCacheC->set('key13', 'value13');
        $simpleCacheC->set('key14', 'value14');
        $simpleCacheC->set('key15', 'value15');
        // there should be fifteen total entries
        $count = $this->redis->dbSize();
        $this->assertEquals(15, $count);
        $simpleCacheA->clearAll();
        // now there should be 0
        $count = $this->redis->dbSize();
        $this->assertEquals(0, $count);
    }//end testCacheClearEverything()
}//end class

?>