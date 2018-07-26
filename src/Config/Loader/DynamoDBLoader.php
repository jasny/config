<?php

declare(strict_types=1);

namespace Jasny\Config\Loader;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\Config\Exception\LoadException;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use BadMethodCallException;

use function Jasny\expect_type;

/**
 * Load config from Dynamo DB.
 */
class DynamoDBLoader implements LoaderInterface
{
    /**
     * Assert that required options are set
     * 
     * @param array $options
     * @return void
     * @throws LoadException
     */
    protected function assertOptions(array $options): void
    {
        if (!isset($options['table'])) {
            throw new BadMethodCallException("Option 'table' is required to load configuration from DynamoDB");
        }
        
        if (!isset($options['key_value'])) {
            throw new BadMethodCallException("Option 'key_value' is required to load configuration from DynamoDB");
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
        if (!isset($options['settings_field'])) {
            $field = $options['key_field'] ?? 'key';
            expect_type($field, 'string', \BadMethodCallException::class);

            unset($data[$field]);
        } else {
            $field = $options['settings_field'];

            if (!isset($data[$field]) && !(bool)($options['optional'] ?? false)) {
                throw new LoadException("DynamoDB item '{$options['key_value']}' doesn't have a '$field' field");
            }

            $data = $data[$field] ?? [];
        }

        return $data;
    }
    
    /**
     * Query Dynamo DB table loading all settings from a single item.
     *
     * @param DynamoDbClient $dynamodb  DB connection
     * @param array          $options
     * @return array|mixed
     */
    protected function loadDataFromItem(DynamoDbClient $dynamodb, array $options)
    {
        $field = $options['key_field'] ?? 'key';
        expect_type($field, 'string', \BadMethodCallException::class);

        $key = $options['key_value'];
        expect_type($key, 'string', \BadMethodCallException::class);

        try {
            $result = $dynamodb->getItem([
                'TableName' => $options["table"],
                'Key' => [
                    $field => ['S' => $key]
                ]
            ]);
            
            if (!isset($result['Item']) && !(bool)($options['optional'] ?? false)) {
                throw new LoadException("Failed to load '{$options['key_value']}' configuration from DynamoDB: "
                    . "No item found with $field '$key'");
            }
            
            $item = isset($result['Item']) ? $result['Item'] : null;
        } catch (DynamoDbException $e) {
            if (!(bool)($options['optional'] ?? false)) {
                throw new LoadException("Failed to load '{$options['key_value']}' configuration from DynamoDB", 0, $e);
            }
        }

        if (!isset($item)) {
            return [];
        }
        
        $marshaler = new Marshaler();
        $data = $marshaler->unmarshalItem($item);
        
        return $this->mapData($data, $options);
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
        expect_type($dynamodb, DynamoDbClient::class);
        $this->assertOptions($options);

        $data = $this->loadDataFromItem($dynamodb, $options);

        if (!is_array($data)) {
            throw new LoadException("DynamoDB '{$options['key_value']}' configuration isn't a key/value map");
        }

        return new Config($data);
    }
}
