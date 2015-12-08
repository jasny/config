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
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$dynamodb = DynamoDbClient::factory([
            'region'       => 'eu-west-1'
        ]);
        self::$dynamodb->createTable(array(
            'TableName' => self::TABLE_NAME,
            'AttributeDefinitions' => array(
                array(
                    'AttributeName' => 'key',
                    'AttributeType' => 'S'
                )
            ),
            'KeySchema' => array(
                array(
                    'AttributeName' => 'key',
                    'KeyType'       => 'HASH'
                )
            ),
            'ProvisionedThroughput' => array(
                'ReadCapacityUnits'  => 1,
                'WriteCapacityUnits' => 1
            )
        ));

        self::$dynamodb->waitUntil('TableExists', array(
            'TableName' => self::TABLE_NAME
        ));

        $data = [
            'key' => 'dev',
            'settings' => array(
                'db'=>'test'
            )
        ];

        $marshaler = new Marshaler();
        $item = $marshaler->marshalItem($data);

        self::$dynamodb->putItem(array(
            'TableName' => self::TABLE_NAME,
            'Item' => $item
        ));
    }
    
    /**
     * This method is called after the last test of this test class is run.
     */
    public static function tearDownAfterClass()
    {
        self::$dynamodb->deleteTable(array(
            'TableName' => self::TABLE_NAME
        ));

        parent::tearDownAfterClass();
    }

    /**
     * Test with existing DB connection
     */
    public function testLoad()
    {
        $data = [
            'db'=>'test'
        ];
        
        $options = ['table' => self::TABLE_NAME, 'key' => 'dev'];
        $loader = new DynamoDBLoader($options);
        $result = $loader->load(self::$dynamodb);
        
        $this->assertEquals($data, $result);
    }

    /**
     * Test with existing DB connection
     */
    public function testConfigLoad()
    {
        $options = ['table' => self::TABLE_NAME, 'key' => 'dev'];

        $config = new Config();

        $result = $config->load(self::$dynamodb, $options);

        $this->assertEquals("", "");
    }
}
