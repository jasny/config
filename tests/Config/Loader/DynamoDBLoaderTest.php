<?php

namespace Jasny\Config\Tests\Loader;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\CommandInterface;
use Jasny\Config;
use Jasny\Config\Loader\DynamoDBLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\Config\Loader\DynamoDBLoader
 */
class DynamoDBLoaderTest extends TestCase
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
        $loader->load($this->dynamodb, ['key_value' => 'foo']);
    }
    
    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Option 'key_value' is required to load configuration from DynamoDB
     */
    public function testAssertKeyOption()
    {
        $loader = new DynamoDBLoader();
        $loader->load($this->dynamodb, ['table' => 'settings']);
    }
    
    /**
     * @expectedException \TypeError
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
        $options = ['table' => 'config', 'key_value' => 'dev'];
        
        $config = $loader->load($this->dynamodb, $options);

        $this->assertEquals(new Config([
            'foo' => 'bar',
            'zoo' => true
        ]), $config);
    }
    
    public function testLoadWithFieldAndKey()
    {
        $this->dynamodb->expects($this->once())->method('getItem')
            ->with([
                'TableName' => 'config',
                'Key' => [
                    'env' => ['S' => 'dev']
                ]
            ])
            ->willReturn([
                'Item' => [
                    'env' => ['S' => 'dev'],
                    'foo' => ['S' => 'bar'],
                    'zoo' => ['BOOL' => true]
                ]
            ]);
        
        $loader = new DynamoDBLoader();
        $options = ['table' => 'config', 'key_field' => 'env', 'key_value' => 'dev'];
        
        $config = $loader->load($this->dynamodb, $options);

        $this->assertEquals(new Config([
            'foo' => 'bar',
            'zoo' => true
        ]), $config);
    }

    /**
     * @expectedException \Jasny\Config\Exception\LoadException
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
        $options = ['table' => 'nonexisting', 'key_value' => 'dev'];
        
        $loader->load($this->dynamodb, $options);
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
        
        $loader = new DynamoDBLoader();
        $options = ['table' => 'nonexisting', 'key_value' => 'dev', 'optional' => true];

        $config = $loader->load($this->dynamodb, $options);
        
        $this->assertEquals(new Config(), $config);
    }

    /**
     * @expectedException \Jasny\Config\Exception\LoadException
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
        $options = ['table' => 'config', 'key_field' => 'foo', 'key_value' => 'nonexisting'];

        $loader->load($this->dynamodb, $options);
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
        $options = ['table' => 'config', 'key_field' => 'foo', 'key_value' => 'nonexisting', 'optional' => true];

        $config = $loader->load($this->dynamodb, $options);
        
        $this->assertEquals(new Config(), $config);
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
        $options = ['table' => 'config', 'key_value' => 'dev', 'settings_field' => 'settings'];

        $config = $loader->load($this->dynamodb, $options);

        $this->assertEquals(new Config([
            'foo' => 'bar',
            'zoo' => true
        ]), $config);
    }

    /**
     * @expectedException \Jasny\Config\Exception\LoadException
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
        $options = ['table' => 'config', 'key_value' => 'dev', 'settings_field' => 'nonexisting'];
        
        $loader->load($this->dynamodb, $options);
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
        $options = ['table' => 'config', 'key_value' => 'dev', 'settings_field' => 'nonexisting', 'optional' => true];
        
        $config = $loader->load($this->dynamodb, $options);

        $this->assertEquals(new Config(), $config);
    }
    
    /**
     * @expectedException \Jasny\Config\Exception\LoadException
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
        $options = ['table' => 'config', 'key_value' => 'dev', 'settings_field' => 'settings'];
        
        $loader->load($this->dynamodb, $options);
    }
}
