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
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

/**
 * Load config from Dynamo DB.
 *
 * <code>
 *   $dynamodb = Aws\DynamoDb\DynamoDbClient::factory([
 *       'region' => 'eu-west-1',
 *       'version' => '2012-08-10'
 *   ]);
 *
 *   $config = new Jasny\Config();
 * 
 *   $config->load($dynamodb, [
 *      'table' => 'config',
 *      'field' => 'key',
 *      'key' => 'staging',
 *      'map' => 'settings'
 *   ]);
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
        if (!isset($options['table'])) {
            throw new \BadMethodCallException("Option 'table' is required to load configuration from DynamoDB");
        }
        
        if (!isset($options['key'])) {
            throw new \BadMethodCallException("Option 'key' is required to load configuration from DynamoDB");
        }
    }

    
    /**
     * Get the map with settings from the data.
     * 
     * @param array $data
     * @param array $options
     * @return array
     */
    protected function mapData(array $data, array $options)
    {
        if (empty($options['map'])) {
            if (!isset($options['field'])) {
                unset($data['key']);
            }
            
            return $data;
        }
        
        $field = $options['map'];

        if (!isset($data[$field]) && empty($options['optional'])) {
            throw new ConfigException("DynamoDB item '{$options['key']}' doesn't have a '$field' field");
        }

        return isset($data[$field]) ? $data[$field] : null;
    }
    
    /**
     * Query Dynamo DB table loading all settings from a single item.
     *
     * @param DynamoDbClient $dynamodb  DB connection
     * @param array          $options
     * @return array
     */
    protected function loadDataFromItem(DynamoDbClient $dynamodb, array $options)
    {
        $field = isset($options["field"]) ? $options["field"] : 'key';
        $key = $options["key"];
        
        try {
            $result = $dynamodb->getItem([
                'TableName' => $options["table"],
                'Key' => [
                    $field => ['S' => $key]
                ]
            ]);
            
            if (!isset($result['Item']) && empty($options['optional'])) {
                throw new ConfigException("Failed to load '{$options['key']}' configuration from DynamoDB: "
                    . "No item found with $field '$key'");
            }
            
            $item = isset($result['Item']) ? $result['Item'] : null;
        } catch (DynamoDbException $e) {
            if (empty($options['optional'])) {
                throw new ConfigException("Failed to load '{$options['key']}' configuration from DynamoDB", 0, $e);
            }
        }

        if (!isset($item)) {
            return null;
        }
        
        $marshaler = new Marshaler();
        $data = $marshaler->unmarshalItem($item);
        
        return isset($data) ? $this->mapData($data, $options) : null;
    }
    
    
    /**
     * Create Config object from data
     * 
     * @param array|null $data
     * @param array      $options
     * @return Config
     */
    protected function createConfig($data, array $options)
    {
        if (!isset($data)) {
            return null;
        }
        
        if (!is_array($data)) {
            throw new ConfigException("DynamoDB '{$options['key']}' configuration isn't a key/value map");
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
        if (!$dynamodb instanceof DynamoDbClient) {
            $type = (is_object($dynamodb) ? get_class($dynamodb) . ' ' : '') . gettype($dynamodb);
            throw new \InvalidArgumentException("Connection isn't a DynamoDbClient object but a $type");
        }
        
        $this->assertOptions($options);

        $data = $this->loadDataFromItem($dynamodb, $options);
        
        return $this->createConfig($data, $options);
    }
}
