<?php

namespace Jasny;

/**
 * Loader stub
 */
class ConfigTestLoader implements Config\Loader
{
    static public $input;
    static public $options;
    static public $data;
    
    public function load($input, $options=array())
    {
        self::$input = $input;
        self::$options = $options;
        
        return self::$data;
    }
}

/**
 * Tests for Jasny\Config.
 * 
 * @package Test
 * @subpackage Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        Config::$loaders['test'] = 'Jasny\ConfigTestLoader';
    }
    
    /**
     * @covers Jasny\Config::i()
     */
    public function testI()
    {
        $c = Config::i();
        $this->assertSame($c, Config::i());
    }
    
    /**
     * @covers Jasny\Config::getLoader()
     */
    public function testGetLoader()
    {
        $c = new Config();
        
        $refl = new \ReflectionObject($c);
        $method = $refl->getMethod('getLoader');
        $method->setAccessible(true);
        
        $loader = $method->invoke($c, __FUNCTION__, array('loader'=>'test', 'arg'=>1));
        $this->assertInstanceOf('Jasny\ConfigTestLoader', $loader);
    }

    /**
     * Get file loader
     * @covers Jasny\Config::getLoader()
     */
    public function testGetLoader_FileLoader()
    {
        $c = new Config();
        
        $refl = new \ReflectionObject($c);
        $method = $refl->getMethod('getLoader');
        $method->setAccessible(true);
        
        $loader = $method->invoke($c, CONFIGTEST_SUPPORT_PATH . '\test.ini');
        $this->assertInstanceOf('Jasny\Config\FileLoader', $loader);
    }
    
    /**
     * Get dir loader
     * @covers Jasny\Config::getLoader()
     */
    public function testGetLoader_DirLoader()
    {
        $c = new Config();
        
        $refl = new \ReflectionObject($c);
        $method = $refl->getMethod('getLoader');
        $method->setAccessible(true);
        
        $loader = $method->invoke($c, CONFIGTEST_SUPPORT_PATH . '/test');
        $this->assertInstanceOf('Jasny\Config\DirLoader', $loader);
    }

    /**
     * @covers Jasny\Config::load()
     * @depends testGetLoader
     */
    public function testLoad()
    {
        ConfigTestLoader::$data = (object)array('a'=>'test');
        
        $c = new Config();
        $c->load(__FUNCTION__, array('loader'=>'test', 'arg'=>1));
        
        $this->assertEquals('test', $c->a);
        $this->assertEquals(__FUNCTION__, ConfigTestLoader::$input);
        $this->assertEquals(array('loader'=>'test', 'arg'=>1), ConfigTestLoader::$options);
    }
    
    /**
     * Load from DSN
     * @covers Jasny\Config::load()
     * @depends testGetLoader
     */
    public function testLoad_DSN()
    {
        ConfigTestLoader::$data = (object)array('a'=>'test');
        
        $c = new Config();
        $c->load('test:' . __FUNCTION__, array('arg'=>2));
        
        $this->assertEquals('test', $c->a);
        $this->assertEquals(__FUNCTION__, ConfigTestLoader::$input);
        $this->assertEquals(array('loader'=>'test', 'arg'=>2), ConfigTestLoader::$options);
    }

}
