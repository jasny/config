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

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\ConfigException;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\ResourceNotFoundException;
use Aws\DynamoDb\Marshaler;

/**
 * Load config from Dynamo DB.
 *
 * <code>
 *   $db = new mysqli($host, $user, $pwd, $dbname);
 *   $dynamodb = Aws\DynamoDb\DynamoDbClient::factory([
 *       'region' => 'eu-west-1'
 *   ]);
 *
 *   $config = new Jasny\Config();
 *   $config->load($dynamodb, ['table' => 'tableName', 'key' => 'keyValue', 'property' => 'settings']);
 * </code>
 */
class DynamoDBLoader implements LoaderInterface
{
    /**
     * Assert that required options are set
     * 
     * @param array $options
     * @throws ConfigException
     */
    protected function assertOptions(array $options)
    {
        if (!isset($options["table"])) {
            throw new ConfigException("Option 'table' is required to load configuration from DynamoDB");
        }
        
        if (!isset($options["table"])) {
            throw new ConfigException("Option 'key' is required to load configuration from DynamoDB");
        }
    }

    /**
     * Query Dynamo DB
     *
     * @param DynamoDbClient $dynamodb  DB connection
     * @param string         $table
     * @param string         $key
     * @return array
     */
    protected function loadData($dynamodb, $table, $key)
    {
        $marshaler = new Marshaler();

        try {
            $result = $dynamodb->getItem([
                'TableName' => $table,
                'Key' => [
                    'key' => ['S' => $key]
                ]
            ]);
            
            if (!isset($result['Item'])) {
                $error = "No record found for key '$key'";
            }
        } catch (ResourceNotFoundException $e) {
            $error = $e->getMessage();
        }

        if (isset($error)) {
            if (empty($this->options['optional'])) {
                throw new ConfigException("Failed to load configuration from DynamoDB: $error");
            }
            
            return null;
        }
        
        return $marshaler->unmarshalItem($result['Item']);
    }
    
    /**
     * Create Config object from data
     * 
     * @param array $data
     * @param array $options
     * @return Config
     */
    protected function createConfig(array $data, array $options)
    {
        if ($options['property']) {
            $property = $options['property'];
            
            if (!isset($data[$property])) {
                if (empty($options['optional'])) {
                    throw new ConfigException("DynamoDB item '{$options['key']}' doesn't have a '$property' property");
                }
                
                return null;
            }
            
            $data = $data[$property];
        }
        
        return new Config($data);
    }
    
    /**
     * Load config from DynamoDB
     *
     * @param DynamoDbClient $dynamodb  DB connection
     * @param array          $options
     * @return Config
     */
    public function load($dynamodb, array $options = [])
    {
        if (!$dynamodb instanceof \Aws\DynamoDb\DynamoDbClient) {
            $type = (is_object($dynamodb) ? get_class($dynamodb) . ' ' : '') . gettype($dynamodb);
            throw new \InvalidArgumentException("Connection isn't a DynamoDbClient object but a $type");
        }
        
        $this->assertOptions($options);

        $data = $this->loadData($dynamodb, $options["table"], $options["key"]);
        
        return isset($data) ? $this->createConfig($data, $options) : null;
    }
}

