<?php

namespace Jasny\Config;

/**
 * Tests for Jasny\Config\IniParser.
 * 
 * @package Test
 * @subpackage Config
 */
class IniParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Jasny\Config\IniParser::parse()
     */
    public function testParse()
    {
        $data = (object)array('grp1' => (object)array('q'=>'ini', 'b'=>27), 'grp2'=>(object)array('a'=>'foobar'));
        
        $parser = new IniParser();
        $this->assertEquals($data, $parser->parse(CONFIGTEST_SUPPORT_PATH . '/test.ini'));
    }
}
