<?php

namespace Jasny\Config;

use Jasny\Config;

require_once 'Loader.php';

/**
 * Load config from MySQL DB.
 * 
 * Options:
 *  - host      DB hostname (defaults to localhost)
 *  - username  DB username
 *  - password  DB password
 *  - port      DB port (defaults to 3306)
 *  - query     Something like "SELECT `option`, `value`, `group` FROM `settings`" (where `group` is optional)
 * 
 * @package Config
 */
class MySQLLoader implements Loader
{
    /**
     * Load a config file
     * 
     * @param \mysqli $connection  DB connection or DSN
     * @param array   $options
     * @return object
     */
    public function load($connection, $options=array())
    {
        if (!$connection instanceof \mysqli) {
            $options = parse_ini_string(str_replace(';', "\n", $connection)) + $options;
            $connection = new \mysqli(isset($options['host']) ? $options['host'] : 'localhost', $options['username'], $options['password'], $options['dbname'], isset($options['port']) ? $options['port'] : null);
            if ($connection->connect_error) throw new \Exception("Failed to connect to db: " . $connection->connect_error);
        }
        
        $data = (object)array();
        
        $result = $connection->query($options['query']);
        if (!$result) throw new \Exception("Config query failed: " . $connection->error);
       
        while ($row = $result->fetch_row()) {
            list($key, $value, $group) = $row + array(3=>null);
            
            if (isset($group)) {
                if (!isset($data->$group)) $data->$group = (object)array();
                $data->$group->$key = $value;
            } else {
                $data->$key = $value;
            }
        }
        
        return $data;
    }
}
