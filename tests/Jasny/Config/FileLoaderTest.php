<?php

namespace Jasny\Config;

use Jasny\Config;

/**
 * Parser stub
 */
class FileTestParser implements Parser
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
        return self::$data;
    }
}

/**
 * Test for Jasny\Config\FileLoader
 */
class FileLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        Config::$loaders['test'] = 'Jasny\Config\FileTestParser';
        FileTestParser::$data = (object)array('grp1' => (object)array('q'=>'test', 'b'=>27), 'grp2'=>(object)array('a'=>'foobar'), 'grp3'=>array('one', 'two', 'three'));
    }
    
    /**
     * Test using test parser
     * @covers Jasny\Config\FileLoader()
     */
    public function testLoad()
    {
        $options = array('opts1'=>99, 'opt2'=>true);
        
        $loader = new FileLoader();
        $result = $loader->load(CONFIGTEST_SUPPORT_PATH . '/test.test', $options);
        
        $this->assertEquals(FileTestParser::$options, $options);
        $this->assertEquals(FileTestParser::$data, $result);
    }
    
    /**
     * Test using ini parser
     * @covers Jasny\Config\FileLoader()
     */
    public function testLoad_Ini()
    {
        $data = (object)array('grp1'=>(object)array('q'=>'ini', 'b'=>27), 'grp2'=>(object)array('a'=>'foobar'));
        
        $loader = new FileLoader();
        $result = $loader->load(CONFIGTEST_SUPPORT_PATH . '/test.ini');
        
        $this->assertEquals($data, $result);
    }

    /**
     * Test using json parser
     * @covers Jasny\Config\FileLoader()
     */
    public function testLoad_Json()
    {
        $data = (object)array('grp1' => (object)array('q'=>'json', 'b'=>27), 'grp2'=>(object)array('a'=>'foobar'), 'grp3'=>array('one', 'two', 'three'));
        
        $loader = new FileLoader();
        $result = $loader->load(CONFIGTEST_SUPPORT_PATH . '/test.json');
        
        $this->assertEquals($data, $result);
    }

    /**
     * Test using json parser
     * @covers Jasny\Config\FileLoader()
     */
    public function testLoad_Force()
    {
        $data = (object)array('grp1' => (object)array('q'=>'test', 'b'=>27), 'grp2'=>(object)array('a'=>'foobar'), 'grp3'=>array('one', 'two', 'three'));
        
        $loader = new FileLoader();
        $result = $loader->load(CONFIGTEST_SUPPORT_PATH . '/test.json', array('loader'=>'test'));
        
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
