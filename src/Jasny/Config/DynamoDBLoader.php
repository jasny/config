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

use Aws\DynamoDb\Marshaler;

/**
 * Load config from Dynamo DB.
 *
 * <code>
 *   $db = new mysqli($host, $user, $pwd, $dbname);
 *   $dynamodb = Aws\DynamoDb\DynamoDbClient::factory([
 *       'region'       => 'eu-west-1'
 *   ]);
 *   $config = new Jasny\Config();
 *   $config->load($dynamodb, ['table' => 'tableName', 'key' => 'keyValue']);
 * </code>
 */
class DynamoDBLoader extends Loader
{
    /**
     * Load config from DynamoDB
     *
     * @param \Aws\DynamoDb\DynamoDbClient $dynamodb DB connection
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
     * @param type $dynamodb
     * @param type $table
     * @param type $key
     * @return type
     */
    protected function loadData($dynamodb, $table, $key)
    {
        $data = array();
        $marshaler = new Marshaler();

        $result = $dynamodb->getItem([
            'TableName' => $table,
            'Key' => [
                'key' => [ 'S' => $key ]
            ]
        ]);

        if(isset($result['Item'])) {
            $data = $marshaler->unmarshalItem($result['Item']);
        }

        return $data['settings'];
    }
}

