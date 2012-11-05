<?php

namespace Jasny\Config;

/**
 * Tests for Jasny\Config\YamlParser.
 * 
 * @package Test
 * @subpackage Config
 */
class YamlParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Jasny\Config\YamlParser::parse()
     */
    public function testParse()
    {
        if (!function_exists('yaml_parse_file') && !function_exists('syck_load')) $this->markTestSkipped("yaml nor syck php extension is loaded");
        
        $data = (object)array('grp1'=>(object)array('q'=>'yaml', 'b'=>27), 'grp2'=>(object)array('a'=>'foobar'), 'grp3'=>array('one', 'two', 'three'));
        
        $parser = new YamlParser();
        $this->assertEquals($data, $parser->parse(CONFIGTEST_SUPPORT_PATH . '/test.yaml'));
    }
}
