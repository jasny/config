<?php
/**
 * Jasny Config - Configure your application.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/config/master/LICENSE MIT
 * @link    https://jasny.github.io/config
 */
/** */
namespace Jasny\Config;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Guzzle\Http\Exception\CurlException;
use Jasny\Config;

/**
 * Test for Jasny\Config\DynamoDBLoader
 */
class DynamoDBLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Database connection
     * @var \Aws\DynamoDB\DynamoDbClient
     */
    static protected $dynamodb;
    /**
     * Const var the table name
     */
    const TABLE_NAME = 'jasny-config-test';
    
    
    /**
     * Connect to dynamodb
     */
    protected static function connect()
    {
        self::$dynamodb = DynamoDbClient::factory([
            'region' => 'local',
            'endpoint' => 'http://localhost:4567',
            'credentials' => [
                'key'    => 'none',
                'secret' => 'none',
            ],
            'request.options' => [
                'connect_timeout' => 3
            ],
            'client.backoff' => false
        ]);
    }
    
    /**
     * Create the table
     */
    protected static function createTable($wait = false)
    {
        $tables = self::$dynamodb->listTables();
        if (in_array(self::TABLE_NAME, $tables['TableNames'])) {
            self::deleteTable(true);
        }
        
        self::$dynamodb->createTable([
            'TableName' => self::TABLE_NAME,
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'key',
                    'AttributeType' => 'S'
                ]
            ],
            'KeySchema' => [
                [
                    'AttributeName' => 'key',
                    'KeyType'       => 'HASH'
                ]
            ],
            'ProvisionedThroughput' => [
                'ReadCapacityUnits'  => 1,
                'WriteCapacityUnits' => 1
            ]
        ]);
        
        if (!$wait) return;
        
        self::$dynamodb->waitUntil('TableExists', [
            'TableName' => self::TABLE_NAME,
            'waiter.interval' => 1,
            'waiter.max_attempts' => 5
        ]);
    }
    
    /**
     * Delete the table
     *
     * @param boolean $wait  Wait until the table is deleted
     */
    protected static function deleteTable($wait = false)
    {
        self::$dynamodb->deleteTable([
            'TableName' => self::TABLE_NAME
        ]);
        
        if (!$wait) return;
        
        self::$dynamodb->waitUntil('TableNotExists', [
            'TableName' => self::TABLE_NAME,
            'waiter.interval' => 1,
            'waiter.max_attempts' => 5
        ]);
    }

    /**
     * Get test config data
     *
     * @return object
     */
    protected static function getTestData()
    {
        $data = new \StdClass;
        $data->qux = 'baz';
        $data->foo = new \StdClass;
        $data->foo->bar = 'qux';
        $data->foo->qux = 'baz';

        return $data;
    }
    
    /**
     * Fill the table with data
     */
    protected static function fillTable()
    {
        $data = [
            'key' => 'dev',
            'settings' => self::getTestData()
        ];

        $marshaler = new Marshaler();
        $item = $marshaler->marshalItem($data);

        self::$dynamodb->putItem([
            'TableName' => self::TABLE_NAME,
            'Item' => $item
        ]);
    }
    
    
    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        try {
            self::connect();
            self::createTable(true);
            self::fillTable();
        } catch (CurlException $e) {
            throw new \PHPUnit_Framework_SkippedTestError("Failed to initialise local dynamodb. Is dynalite running on localhost:4567?");
        }
    }
    
    /**
     * This method is called after the last test of this test class is run.
     */
    public static function tearDownAfterClass()
    {
        if (isset(self::$dynamodb)) {
            self::deleteTable();
        }

        parent::tearDownAfterClass();
    }


    /**
     * Test with existing DB connection
     */
    public function testLoad()
    {
        $options = ['table' => self::TABLE_NAME, 'key' => 'dev'];
        $loader = new DynamoDBLoader($options);
        $result = $loader->load(self::$dynamodb);

        $this->assertEquals(self::getTestData(), $result);
    }

    /**
     * Test through config->load
     */
    public function testConfigLoad()
    {
        $options = ['table' => self::TABLE_NAME, 'key' => 'dev'];
        $config = new Config();
        $config->load(self::$dynamodb, $options);

        $expected = self::getTestData();

        $this->assertEquals($expected->qux, $config->qux);
        $this->assertEquals($expected->foo, $config->foo);
    }

    /**
     * Test with non-existing DB table
     *
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Failed to load configuration: Requested resource not found
     */
    public function testLoad_NonExistingTable()
    {
        $options = ['table' => 'nonexisting', 'key' => 'dev'];
        $loader = new DynamoDBLoader($options);
        
        $loader->load(self::$dynamodb);
    }

    /**
     * Test with non-existing DB table with 'optional' option
     */
    public function testLoad_NonExistingTable_Optional()
    {
        $options = ['table' => 'nonexisting', 'key' => 'dev', 'optional' => true];
        $loader = new DynamoDBLoader($options);
        
        $result = $loader->load(self::$dynamodb);
        $this->assertNull($result);
    }

    /**
     * Test with non-existing DB table
     *
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Failed to load configuration: No record found for key 'nonexisting'
     */
    public function testLoad_NonExistingKey()
    {
        $options = ['table' => self::TABLE_NAME, 'key' => 'nonexisting'];
        $loader = new DynamoDBLoader($options);
        
        $result = $loader->load(self::$dynamodb);
    }

    /**
     * Test with non-existing DB table
     */
    public function testLoad_NonExistingKey_Optional()
    {
        $options = ['table' => self::TABLE_NAME, 'key' => 'nonexisting', 'optional' => true];
        $loader = new DynamoDBLoader($options);
        
        $result = $loader->load(self::$dynamodb);
        $this->assertNull($result);
    }
}

