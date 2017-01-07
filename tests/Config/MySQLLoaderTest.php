<?php

namespace Jasny\Config;

/**
 * Test for Jasny\Config\MySQLLoader
 */
class MySQLLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Database connection
     * @var \mysqli
     */
    static protected $db;
    
    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        // Setup DB
        mysqli_report(MYSQLI_REPORT_STRICT);
        try {
            self::$db = new \mysqli(
                ini_get('mysqli.default_host'),
                ini_get('mysqli.default_user') ?: 'root',
                ini_get('mysqli.default_pw')
            );
        } catch (\mysqli_sql_exception $e) {
            throw new \PHPUnit_Framework_SkippedTestError("Failed to connect to mysql: " . $e->getMessage());
        }

        $queries = [
            "CREATE DATABASE `jasny_config_test`",
            "USE `jasny_config_test`",
            "CREATE TABLE `settings` (`option` VARCHAR(32) NOT NULL, `value` VARCHAR(255) NOT NULL,"
                . " `group` VARCHAR(32) DEFAULT NULL)",
            "INSERT INTO `settings` VALUES ('opt1', 'test', NULL), ('opt2', 'jasny', NULL), ('q', 'mysqli', 'grp1'),"
                . " ('b', 27, 'grp1'), ('a', 'foobar', 'grp2')"
        ];
        
        foreach ($queries as $query) {
            try {
                self::$db->query($query);
            } catch (\mysqli_sql_exception $e) {
                throw new \PHPUnit_Framework_SkippedTestError("Failed to initialise DB: " . $e->getMessage());
            }
        }
    }
    
    /**
     * This method is called after the last test of this test class is run.
     */
    public static function tearDownAfterClass()
    {
        self::$db->query("DROP DATABASE IF EXISTS `jasny_config_test`");
        self::$db = null;
        
        parent::tearDownAfterClass();
    }

    /**
     * Test with existing DB connection
     */
    public function testLoad()
    {
        $data = (object)[
            'opt1'=>'test',
            'opt2'=>'jasny',
            'grp1'=>(object)['q'=>'mysqli', 'b'=>27],
            'grp2'=>(object)['a'=>'foobar']
        ];
        
        $loader = new MySQLLoader("SELECT `option`, `value`, `group` FROM `settings`");
        $result = $loader->load(self::$db);
        
        $this->assertEquals($data, $result);
    }
}
