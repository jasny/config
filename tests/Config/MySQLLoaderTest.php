<?php

namespace Jasny\Config;

use Jasny\Config;
use Jasny\Config\MySQLLoader;
use Jasny\ConfigException;

/**
 * Test for Jasny\Config\MySQLLoader
 */
class MySQLLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test with existing DB connection
     */
    public function testLoad()
    {
        if (!class_exists('mysqli')) {
            $this->markTestSkipped("mysqli extension isn't loaded");
        }
        
        $db = $this->createMock(\mysqli::class);
        $result = $this->createMock(\mysqli_result::class);
        
        $db->expects($this->once())->method('query')
            ->with("SELECT `option`, `value` FROM `setting`")
            ->willReturn($result);
        
        $result->expects($this->exactly(3))->method('fetch_row')->willReturnOnConsecutiveCalls(
            ['foo', 'bar'],
            ['zoo', 'quz'],
            null
        );
        
        $loader = new MySQLLoader();
        
        $config = $loader->load($db, ['query' => "SELECT `option`, `value` FROM `setting`"]);
        
        $this->assertEquals($config, new Config([
            'foo' => 'bar',
            'zoo' => 'quz'
        ]));
    }
    
    /**
     * Test with existing DB connection
     */
    public function testLoadWithGroup()
    {
        if (!class_exists('mysqli')) {
            $this->markTestSkipped("mysqli extension isn't loaded");
        }
        
        $db = $this->createMock(\mysqli::class);
        $result = $this->createMock(\mysqli_result::class);
        
        $db->expects($this->once())->method('query')
            ->with("SELECT `option`, `value`, `group` FROM `setting`")
            ->willReturn($result);
        
        $result->expects($this->exactly(5))->method('fetch_row')->willReturnOnConsecutiveCalls(
            ['foo', 'bar'],
            ['bear', 'bollo', 'animal'],
            ['cow', 'cindy', 'animal'],
            ['wood', 'oak', 'material'],
            null
        );
        
        $loader = new MySQLLoader();
        
        $config = $loader->load($db, ['query' => "SELECT `option`, `value`, `group` FROM `setting`"]);
        
        $this->assertEquals($config, new Config([
            'foo' => 'bar',
            'animal' => [
                'bear' => 'bollo',
                'cow' => 'cindy'
            ],
            'material' => [
                'wood' => 'oak'
            ]
        ]));
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected a mysqli object not a boolean
     */
    public function testLoadWithInvalidConnection()
    {
        $loader = new MySQLLoader();
        $loader->load(false, ['query' => "SELECT `option`, `value` FROM `setting`"]);
    }
    
    /**
     * @expectedException Jasny\ConfigException
     * @expectedExceptionMessage Option 'query' is required to load configuration from MySQL
     */
    public function testLoadWithoutQuery()
    {
        $db = $this->createMock(\mysqli::class);
        
        $loader = new MySQLLoader();
        $loader->load($db);
    }
    
    /**
     * @expectedException Jasny\ConfigException
     * @expectedExceptionMessage Failed to load configuration from MySQL: query failed
     */
    public function testLoadWithFailedQuery()
    {
        $db = $this->getMockBuilder(\mysqli::class)->disableProxyingToOriginalMethods()->getMock();
        $db->expects($this->once())->method('query')->with('foo')->willReturn(false);
        
        $loader = new MySQLLoader();
        $loader->load($db, ['query' => 'foo']);
    }
    
    /**
     * @expectedException Jasny\ConfigException
     * @expectedExceptionMessage Failed to load configuration from MySQL: query failed
     */
    public function testLoadWithFailedQueryException()
    {
        $db = $this->createMock(\mysqli::class);
        $db->expects($this->once())->method('query')->with('foo')
            ->willThrowException(new \mysqli_sql_exception("Query parsing error"));
        
        $loader = new MySQLLoader();
        $loader->load($db, ['query' => 'foo']);
    }
}
