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
use Aws\DynamoDb\Exception\ResourceNotFoundException;
use Aws\DynamoDb\Marshaler;

/**
 * Load config from Dynamo DB.
 *
 * <code>
 *   $db = new mysqli($host, $user, $pwd, $dbname);
 *   $dynamodb = Aws\DynamoDb\DynamoDbClient::factory([
 *       'region'       => 'eu-west-1'
 *   ]);
 *
 *   $config = new Jasny\Config();
 *   $config->load($dynamodb, ['table' => 'tableName', 'key' => 'keyValue']);
 * </code>
 */
class DynamoDBLoader extends Loader
{
    /**
     * Load config from DynamoDB
     *
     * @param \Aws\DynamoDb\DynamoDbClient $dynamodb  DB connection
     * @return object
     */
    public function load($dynamodb)
    {
        if (!$dynamodb instanceof \Aws\DynamoDb\DynamoDbClient) {
            trigger_error("Failed to load config: connection isn't a DynamoDbClient object", E_USER_WARNING);
            return null;
        }

        return $this->loadData($dynamodb, $this->options["table"], $this->options["key"]);
    }

    /**
     * Query Dynamo DB
     *
     * @param \Aws\DynamoDb\DynamoDbClient $dynamodb  DB connection
     * @param string                       $table
     * @param string                       $key
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
                trigger_error("Failed to load configuration: $error", E_USER_WARNING);
            }
            
            return null;
        }
        
        $item = $marshaler->unmarshalItem($result['Item']);
        $data = null;
        if(isset($item['settings'])) {
            $data = $item['settings'];
        }

        return Config::objectify($data);
    }
}

