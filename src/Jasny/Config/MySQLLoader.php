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

/**
 * Load config from MySQL DB.
 * 
 * <code>
 *   $db = new mysqli($host, $user, $pwd, $dbname);<br/>
 *   $config = new Jasny\Config();
 *   $config->load($db, "SELECT `option`, `value`, `group` FROM `settings`"); // (`group` is optional)
 * </code>
 */
class MySQLLoader extends Loader
{
    /**
     * Class constructor
     * 
     * @param string $query   Query string
     */
    public function __construct($query)
    {
        $options = is_string($query) ? compact('query') : $query;
        parent::__construct($options);
    }
    
    /**
     * Load config from MySQL
     * 
     * @param \mysqli $connection  DB connection or DSN
     * @return object
     */
    public function load($connection)
    {
        if (!$connection instanceof \mysqli) {
            trigger_error("Failed to load config: connection isn't a mysqli object", E_USER_WARNING);
            return null;
        }
        
        return $this->loadData($connection, $this->options['query']);
    }
    
    /**
     * Query MySQL DB
     * 
     * @param type $connection
     * @param type $query
     * @return type
     */
    protected function loadData($connection, $query)
    {
        $data = (object)array();
        
        try {
            $result = $connection->query($query);
            if (!$result) trigger_error("Config query failed: " . $connection->error, E_USER_WARNING);
        } catch (\Excpetion $e) {
            trigger_error("Config query failed: " . $e->getMessage(), E_USER_WARNING);
        }
       
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
