<?php

namespace LaminasTest\Cache\Storage\Adapter;

use Laminas\Cache\Storage\Adapter\RedisResourceManager;
use PHPUnit\Framework\TestCase;

use function getenv;

/**
 * PHPUnit test case
 */

/**
 * @group      Laminas_Cache
 * @covers Laminas\Cache\Storage\Adapter\RedisResourceManager
 */
class RedisResourceManagerTest extends TestCase
{
    /**
     * The resource manager
     *
     * @var RedisResourceManager
     */
    protected $resourceManager;

    public function setUp(): void
    {
        $this->resourceManager = new RedisResourceManager();
    }

    /**
     * @group 6495
     */
    public function testSetServerWithPasswordInUri()
    {
        $dummyResId = '1234567890';
        $server     = 'redis://dummyuser:dummypass@testhost:1234';

        $this->resourceManager->setServer($dummyResId, $server);

        $server = $this->resourceManager->getServer($dummyResId);

        $this->assertEquals('testhost', $server['host']);
        $this->assertEquals(1234, $server['port']);
        $this->assertEquals('dummypass', $this->resourceManager->getPassword($dummyResId));
    }

    /**
     * @group 6495
     */
    public function testSetServerWithPasswordInParameters()
    {
        $server      = 'redis://dummyuser:dummypass@testhost:1234';
        $dummyResId2 = '12345678901';
        $resource    = [
            'persistent_id' => 'my_connection_name',
            'server'        => $server,
            'password'      => 'abcd1234',
        ];

        $this->resourceManager->setResource($dummyResId2, $resource);

        $server = $this->resourceManager->getServer($dummyResId2);

        $this->assertEquals('testhost', $server['host']);
        $this->assertEquals(1234, $server['port']);
        $this->assertEquals('abcd1234', $this->resourceManager->getPassword($dummyResId2));
    }

    /**
     * @group 6495
     */
    public function testSetServerWithPasswordInUriShouldNotOverridePreviousResource()
    {
        $server      = 'redis://dummyuser:dummypass@testhost:1234';
        $server2     = 'redis://dummyuser:dummypass@testhost2:1234';
        $dummyResId2 = '12345678901';
        $resource    = [
            'persistent_id' => 'my_connection_name',
            'server'        => $server,
            'password'      => 'abcd1234',
        ];

        $this->resourceManager->setResource($dummyResId2, $resource);
        $this->resourceManager->setServer($dummyResId2, $server2);

        $server = $this->resourceManager->getServer($dummyResId2);

        $this->assertEquals('testhost2', $server['host']);
        $this->assertEquals(1234, $server['port']);
        // Password should not be overridden
        $this->assertEquals('abcd1234', $this->resourceManager->getPassword($dummyResId2));
    }

    /**
     * Test with 'persistent_id'
     */
    public function testValidPersistentId()
    {
        $resourceId           = 'testValidPersistentId';
        $resource             = [
            'persistent_id' => 'my_connection_name',
            'server'        => [
                'host' => getenv('TESTS_LAMINAS_CACHE_REDIS_HOST') ?: 'localhost',
                'port' => getenv('TESTS_LAMINAS_CACHE_REDIS_PORT') ?: 6379,
            ],
        ];
        $expectedPersistentId = 'my_connection_name';
        $this->resourceManager->setResource($resourceId, $resource);
        $this->assertSame($expectedPersistentId, $this->resourceManager->getPersistentId($resourceId));
        $this->assertInstanceOf('Redis', $this->resourceManager->getResource($resourceId));
    }

    /**
     * Test with 'persistend_id' instead of 'persistent_id'
     */
    public function testNotValidPersistentIdOptionName()
    {
        $resourceId           = 'testNotValidPersistentId';
        $resource             = [
            'persistend_id' => 'my_connection_name',
            'server'        => [
                'host' => getenv('TESTS_LAMINAS_CACHE_REDIS_HOST') ?: 'localhost',
                'port' => getenv('TESTS_LAMINAS_CACHE_REDIS_PORT') ?: 6379,
            ],
        ];
        $expectedPersistentId = 'my_connection_name';
        $this->resourceManager->setResource($resourceId, $resource);

        $this->assertNotSame($expectedPersistentId, $this->resourceManager->getPersistentId($resourceId));
        $this->assertEmpty($this->resourceManager->getPersistentId($resourceId));
        $this->assertInstanceOf('Redis', $this->resourceManager->getResource($resourceId));
    }

    public function testGetVersion()
    {
        $resourceId = __FUNCTION__;
        $resource   = [
            'server' => [
                'host' => getenv('TESTS_LAMINAS_CACHE_REDIS_HOST') ?: 'localhost',
                'port' => getenv('TESTS_LAMINAS_CACHE_REDIS_PORT') ?: 6379,
            ],
        ];
        $this->resourceManager->setResource($resourceId, $resource);

        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+/', $this->resourceManager->getVersion($resourceId));
    }

    public function testGetMajorVersion()
    {
        $resourceId = __FUNCTION__;
        $resource   = [
            'server' => [
                'host' => getenv('TESTS_LAMINAS_CACHE_REDIS_HOST') ?: 'localhost',
                'port' => getenv('TESTS_LAMINAS_CACHE_REDIS_PORT') ?: 6379,
            ],
        ];
        $this->resourceManager->setResource($resourceId, $resource);

        $this->assertGreaterThan(0, $this->resourceManager->getMajorVersion($resourceId));
    }
}
