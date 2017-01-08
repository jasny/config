<?php

namespace Jasny\Config;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\ConfigException;

/**
 * Load config from MySQL DB.
 *
 * <code>
 *   $db = new mysqli($host, $user, $pwd, $dbname);
 *   $config = new Jasny\Config();
 *   $config->load($db, ['query' => "SELECT `key`, `value`, `group` FROM `settings`"]); // (`group` is optional)
 * </code>
 */
class MySQLLoader implements LoaderInterface
{
    /**
     * Load config from MySQL
     *
     * @param mysqli $connection  DB connection
     * @param array  $options
     * @return object
     */
    public function load($connection, array $options = [])
    {
        if (!$connection instanceof \mysqli) {
            $type = (is_object($connection) ? get_class($connection) . ' ' : '') . gettype($connection);
            throw new \InvalidArgumentException("Expected a mysqli object not a $type");
        }
        
        if (!isset($options['query'])) {
            throw new ConfigException("Option 'query' is required to load configuration from MySQL");
        }
        
        $data = $this->loadData($connection, $options['query']);
        
        return new Config($data);
    }
    
    /**
     * Query MySQL DB
     *
     * @param \mysqli $connection
     * @param string $query
     * @return array
     */
    protected function loadData(\mysqli $connection, $query)
    {
        try {
            $result = $connection->query($query);
            
            if (!$result) {
                throw new \Exception($connection->error);
            }
        } catch (\Exception $e) {
            throw new ConfigException("Failed to load configuration from MySQL: query failed", 0, $e);
        }
        
        return $this->processResult($result);
    }
    
    /**
     * Process the database result;
     * 
     * @param \mysqli_result $result
     * @return array
     */
    protected function processResult(\mysqli_result $result)
    {
        $data = [];
       
        while ($row = $result->fetch_row()) {
            list($key, $value, $group) = $row + array_fill(0, 3, null);
            
            if (isset($group) && $group !== '') {
                if (!isset($data[$group])) {
                    $data[$group] = [];
                }
                
                $data[$group][$key] = $value;
            } else {
                $data[$key] = $value;
            }
        }
        
        return $data;
    }
}
