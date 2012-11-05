<?php

namespace Jasny\Config;

/**
 * Tests for Jasny\Config\JsonParser.
 * 
 * @package Test
 * @subpackage Config
 */
class JsonParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Jasny\Config\JsonParser::parse()
     */
    public function testParse()
    {
        if (!function_exists('json_decode')) $this->markTestSkipped("json php extension not loaded");
        
        $data = (object)array('grp1' => (object)array('q'=>'json', 'b'=>27), 'grp2'=>(object)array('a'=>'foobar'), 'grp3'=>array('one', 'two', 'three'));
        
        $parser = new JsonParser();
        $result = $parser->parse(CONFIGTEST_SUPPORT_PATH . '/test.json');
        
        $this->assertEquals($data, $result);
    }
}
