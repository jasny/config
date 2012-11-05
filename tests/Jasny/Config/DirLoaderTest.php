<?php

namespace Jasny\Config;

use Jasny\Config;

/**
 * Parser stub
 */
class DirTestParser implements Parser
{
    static public $options;
    static public $data;
    
    public function __construct($options = array())
    {
        self::$options = $options;
    }
    
    public function parse($input)
    {
        $input = preg_replace('/^' . preg_quote(CONFIGTEST_SUPPORT_PATH . '/test/', '/') . '|\.test$/', '', $input);
        return self::$data[$input];
    }
}

/**
 * Test for Jasny\Config\DirLoader
 */
class DirLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        Config::$loaders['test'] = 'Jasny\Config\DirTestParser';
        DirTestParser::$data = array('grp1' => (object)array('q'=>'abc', 'b'=>27), 'grp2'=>(object)array('a'=>'foobar'), 'grp3'=>array('one', 'two', 'three'), 'section1/foo'=>'ABC', 'section1/bar'=>'XYZ');
    }
    
    /**
     * Test using test parser
     * @covers Jasny\Config\DirLoader::Load()
     */
    public function testLoad()
    {
        $options = array('opts1'=>99, 'opt2'=>true);
        $data = (object)array('grp1' => (object)array('q'=>'abc', 'b'=>27), 'grp2'=>(object)array('a'=>'foobar'), 'grp3'=>array('one', 'two', 'three'), 'section1'=>(object)array('foo'=>'ABC', 'bar'=>'XYZ'));
        
        $loader = new DirLoader();
        $result = $loader->load(CONFIGTEST_SUPPORT_PATH . '/test', $options);
        
        $this->assertEquals(DirTestParser::$options, $options);
        $this->assertEquals($data, $result);
    }

    /**
     * Test using json parser
     * @covers Jasny\Config\DirLoader::Load()
     */
    public function testLoad_Any()
    {
        $data = (object)array('grp1'=>(object)array('q'=>'abc', 'b'=>27), 'grp2'=>(object)array('a'=>'foobar'), 'grp3'=>array('one', 'two', 'three'));
        
        $loader = new DirLoader();
        $result = $loader->load(CONFIGTEST_SUPPORT_PATH . '/test-any');
        
        $this->assertEquals($data, $result);
    }
    
    /**
     * Test using ini parser
     * @covers Jasny\Config\DirLoader::Load()
     */
    public function testLoad_Ini()
    {
        $data = (object)array('grp1'=>(object)array('q'=>'abc', 'b'=>27), 'grp2'=>(object)array('a'=>'foobar'));
        
        $loader = new DirLoader();
        $result = $loader->load(CONFIGTEST_SUPPORT_PATH . '/test-any', array('loader'=>'ini'));
        
        $this->assertEquals($data, $result);
    }

    /**
     * Test using json parser
     * @covers Jasny\Config\DirLoader::Load()
     */
    public function testLoad_Json()
    {
        $data = (object)array('grp3'=>array('one', 'two', 'three'));
        
        $loader = new DirLoader();
        $result = $loader->load(CONFIGTEST_SUPPORT_PATH . '/test-any', array('loader'=>'json'));
        
        $this->assertEquals($data, $result);
    }
    
    /**
     * Test loading a non-existant file
     * @covers Jasny\Config\DirLoader::Load()
     */
    public function testLoad_Optional()
    {
        $loader = new DirLoader();
        $this->assertNull($loader->load(uniqid() . '.fake', array('optional'=>true)));
    }
}
