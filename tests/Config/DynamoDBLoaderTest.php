<?php

namespace Jasny\Config;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\CommandInterface;
use Jasny\Config;

/**
 * @covers DynamoDBLoader
 */
class DynamoDBLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $dynamodb;
    
    public function setUp()
    {
        $this->dynamodb = $this->getMockBuilder(DynamoDbClient::class)
            ->disableProxyingToOriginalMethods()
            ->disableOriginalConstructor()
            ->setMethods(['getItem', 'scan'])
            ->getMock();
    }

    
    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Option 'table' is required to load configuration from DynamoDB
     */
    public function testAssertTableOption()
    {
        $loader = new DynamoDBLoader();
        $loader->load($this->dynamodb, ['key' => 'foo']);
    }
    
    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Option 'key' is required to load configuration from DynamoDB
     */
    public function testAssertKeyOption()
    {
        $loader = new DynamoDBLoader();
        $loader->load($this->dynamodb, ['table' => 'settings']);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLoadWithInvalidConnection()
    {
        $loader = new DynamoDBLoader();
        $loader->load(false);
    }
    
    
    public function testLoadWithKey()
    {
        $this->dynamodb->expects($this->once())->method('getItem')
            ->with([
                'TableName' => 'config',
                'Key' => [
                    'key' => ['S' => 'dev']
                ]
            ])
            ->willReturn([
                'Item' => [
                    'key' => ['S' => 'dev'],
                    'foo' => ['S' => 'bar'],
                    'zoo' => ['BOOL' => true]
                ]
            ]);
        
        $loader = new DynamoDBLoader();
        
        $config = $loader->load($this->dynamodb, ['table' => 'config', 'key' => 'dev']);

        $this->assertEquals(new Config([
            'key' => 'dev',
            'foo' => 'bar',
            'zoo' => true
        ]), $config);
    }

    /**
     * @expectedException Jasny\ConfigException
     * @expectedExceptionMessage Failed to load 'dev' configuration from DynamoDB
     */
    public function testLoadWithKeyAndNonExistingTable()
    {
        $this->dynamodb->expects($this->once())->method('getItem')
            ->with([
                'TableName' => 'nonexisting',
                'Key' => [
                    'key' => ['S' => 'dev']
                ]
            ])
            ->willThrowException(new DynamoDbException("Not found", $this->createMock(CommandInterface::class)));
        
        $loader = new DynamoDBLoader();
        
        $loader->load($this->dynamodb, ['table' => 'nonexisting', 'key' => 'dev']);
    }

    public function testLoadWithKeyAndNonExistingTableAndOptional()
    {
        $this->dynamodb->expects($this->once())->method('getItem')
            ->with([
                'TableName' => 'nonexisting',
                'Key' => [
                    'key' => ['S' => 'dev']
                ]
            ])
            ->willThrowException(new DynamoDbException("Not found", $this->createMock(CommandInterface::class)));
        
        $loader = new DynamoDBLoader($options);

        $config = $loader->load($this->dynamodb, ['table' => 'nonexisting', 'key' => 'dev', 'optional' => true]);
        
        $this->assertNull($config);
    }

    /**
     * @expectedException Jasny\ConfigException
     * @expectedExceptionMessage Failed to load 'nonexisting' configuration from DynamoDB: No item found with foo 'nonexisting'
     */
    public function testLoadWithNonExistingKey()
    {
        $this->dynamodb->expects($this->once())->method('getItem')
            ->with([
                'TableName' => 'config',
                'Key' => [
                    'foo' => ['S' => 'nonexisting']
                ]
            ])
            ->willReturn([]);
        
        $loader = new DynamoDBLoader();

        $loader->load($this->dynamodb, ['table' => 'config', 'field' => 'foo', 'key' => 'nonexisting']);
    }

    public function testLoadWithNonExistingKeyAndOptional()
    {
        $this->dynamodb->expects($this->once())->method('getItem')
            ->with([
                'TableName' => 'config',
                'Key' => [
                    'foo' => ['S' => 'nonexisting']
                ]
            ])
            ->willReturn([]);
        
        $loader = new DynamoDBLoader();

        $config = $loader->load($this->dynamodb, ['table' => 'config', 'field' => 'foo', 'key' => 'nonexisting',
            'optional' => true]);
        
        $this->assertNull($config);
    }

    
    public function testLoadWithMap()
    {
        $this->dynamodb->expects($this->once())->method('getItem')
            ->with([
                'TableName' => 'config',
                'Key' => [
                    'key' => ['S' => 'dev']
                ]
            ])
            ->willReturn([
                'Item' => [
                    'key' => ['S' => 'dev'],
                    'settings' => ['M' => [
                        'foo' => ['S' => 'bar'],
                        'zoo' => ['BOOL' => true]
                    ]]
                ]
            ]);
        
        $loader = new DynamoDBLoader();
        
        $config = $loader->load($this->dynamodb, ['table' => 'config', 'key' => 'dev', 'map' => 'settings']);

        $this->assertEquals(new Config([
            'foo' => 'bar',
            'zoo' => true
        ]), $config);
    }

    /**
     * @expectedException Jasny\ConfigException
     * @expectedExceptionMessage DynamoDB item 'dev' doesn't have a 'nonexisting' field
     */
    public function testLoadWithNonExistingMap()
    {
        $this->dynamodb->expects($this->once())->method('getItem')
            ->with([
                'TableName' => 'config',
                'Key' => [
                    'key' => ['S' => 'dev']
                ]
            ])
            ->willReturn([
                'Item' => [
                    'key' => ['S' => 'dev']
                ]
            ]);
        
        $loader = new DynamoDBLoader();
        
        $loader->load($this->dynamodb, ['table' => 'config', 'key' => 'dev', 'map' => 'nonexisting']);
    }
    
    public function testLoadWithNonExistingMapAndOptional()
    {
        $this->dynamodb->expects($this->once())->method('getItem')
            ->with([
                'TableName' => 'config',
                'Key' => [
                    'key' => ['S' => 'dev']
                ]
            ])
            ->willReturn([
                'Item' => [
                    'key' => ['S' => 'dev']
                ]
            ]);
        
        $loader = new DynamoDBLoader();
        
        $config = $loader->load($this->dynamodb, ['table' => 'config', 'key' => 'dev', 'map' => 'nonexisting',
            'optional' => true]);

        $this->assertNull($config);
    }
    
    /**
     * @expectedException Jasny\ConfigException
     * @expectedExceptionMessage DynamoDB 'dev' configuration isn't a key/value map
     */
    public function testLoadWithInvalidMap()
    {
        $this->dynamodb->expects($this->once())->method('getItem')
            ->with([
                'TableName' => 'config',
                'Key' => [
                    'key' => ['S' => 'dev']
                ]
            ])
            ->willReturn([
                'Item' => [
                    'key' => ['S' => 'dev'],
                    'settings' => ['S' => 'foo']
                ]
            ]);
        
        $loader = new DynamoDBLoader();
        
        $loader->load($this->dynamodb, ['table' => 'config', 'key' => 'dev', 'map' => 'settings']);
    }
}
