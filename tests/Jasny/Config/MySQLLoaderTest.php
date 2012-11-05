<?php

namespace Jasny\Config;

use Jasny\Config;

/**
 * Test for Jasny\Config\MySQLLoader
 */
class MySQLLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Create new databases.
     */
    protected static function createDB()
    {
        // Setup DB
        $m = new \mysqli(ini_get('mysqli.default_host'), ini_get('mysqli.default_user') ?: 'root', ini_get('mysqli.default_pw'));
        if ($m->connect_error) throw new \PHPUnit_Framework_SkippedTestError("Failed to connect to mysql: " . $m->connect_error);

        $queries = array(
            "CREATE DATABASE `configtest`",
            "USE `configtest`",
            "CREATE TABLE `settings` (`option` VARCHAR(32) NOT NULL, `value` VARCHAR(255) NOT NULL, `group` VARCHAR(32) DEFAULT NULL)",
            "INSERT INTO `settings` VALUES ('opt1', 'test', NULL), ('opt2', 'jasny', NULL), ('q', 'mysqli', 'grp1'), ('b', 27, 'grp1'), ('a', 'foobar', 'grp2')");
        
        foreach ($queries as $query) {
            if (!$m->query($query)) throw new \PHPUnit_Framework_SkippedTestError("Failed to initialise DBs: " . $m->error);
        }
    }

    /**
     * Drop databases.
     * Please call dropDB if you've modified data.
     */
    protected static function dropDB()
    {
        $m = new \mysqli(ini_get('mysqli.default_host'), ini_get('mysqli.default_user') ?: 'root', ini_get('mysqli.default_pw'));
        if (!$m->connect_error) $m->query("DROP DATABASE IF EXISTS `configtest`");
    }

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::createDB();
    }
    
    /**
     * This method is called after the last test of this test class is run.
     */
    public static function tearDownAfterClass()
    {
        self::dropDB();
        parent::tearDownAfterClass();
    }

    /**
     * Test with existing DB connection
     * @covers Jasny\Config\MySQLLoader()
     */
    public function testLoad()
    {
        $data = (object)array('opt1'=>'test', 'opt2'=>'jasny', 'grp1'=>(object)array('q'=>'mysqli', 'b'=>27), 'grp2'=>(object)array('a'=>'foobar'));
        
        $m = new \mysqli(ini_get('mysqli.default_host'), ini_get('mysqli.default_user') ?: 'root', ini_get('mysqli.default_pw'), 'configtest');
        $loader = new MySQLLoader();
        
        $this->assertEquals($data, $loader->load($m, array('query'=>"SELECT `option`, `value`, `group` FROM `settings`")));
    }

    /**
     * Test and create a DB connection
     * @covers Jasny\Config\MySQLLoader()
     */
    public function testLoad_DSN()
    {
        $data = (object)array('opt1'=>'test', 'opt2'=>'jasny', 'grp1'=>(object)array('q'=>'mysqli', 'b'=>27), 'grp2'=>(object)array('a'=>'foobar'));
        
        $dsn = "host=" . ini_get('mysqli.default_host') . ";user=" . (ini_get('mysqli.default_user') ?: 'root')  . ";password=" . ini_get('mysqli.default_pw')  . ";dbname=configtest";
        $loader = new MySQLLoader();
        
        $this->assertEquals($data, $loader->load($dsn, array('query'=>"SELECT `option`, `value`, `group` FROM `settings`")));
    }
}
