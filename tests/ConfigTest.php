<?php
/**
 * Jasny Config - Configure your application.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/config/master/LICENSE MIT
 * @link    https://jasny.github.io/config
 */
/** */
namespace Jasny;

/**
 * Tests for Jasny\Config.
 * 
 * @package Test
 * @subpackage Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $config;
    
    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        Config::$loaders['test'] = 'Jasny\ConfigTestLoader';
        $this->config = new Config();
    }
    
    /**
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset(Config::$loaders['test']);
        unset($this->config);
        
        ConfigTestLoader::reset();
    }
    
    
    /**
     * Test Jasny\Config::getLoader()
     */
    public function testGetLoader()
    {
        $loader = $this->config->getLoader('', ['loader'=>'test', 'arg'=>1]);
        
        $this->assertInstanceOf('Jasny\ConfigTestLoader', $loader);
        $this->assertEquals(['loader'=>'test', 'arg'=>1], ConfigTestLoader::$opts);
    }

    /**
     * Test Jasny\Config::getLoader() with a filename
     */
    public function testGetLoader_File()
    {
        $loader = $this->config->getLoader('foo.test', ['arg'=>1]);
        
        $this->assertInstanceOf('Jasny\ConfigTestLoader', $loader);
        $this->assertEquals(['arg'=>1], ConfigTestLoader::$opts);
    }

    /**
     * Get dir loader
     * @covers Jasny\Config::getLoader()
     */
    public function testGetLoader_DirLoader()
    {
        $loader = $this->config->getLoader(CONFIGTEST_SUPPORT_PATH . '/test');
        $this->assertInstanceOf('Jasny\Config\DirLoader', $loader);
    }

    /**
     * @covers Jasny\Config::load()
     * @depends testGetLoader
     */
    public function testLoad()
    {
        ConfigTestLoader::$data = (object)['a'=>'test'];
        
        $c = new Config();
        $c->load(__FUNCTION__, ['loader'=>'test', 'arg'=>1]);
        
        $this->assertEquals('test', $c->a);
        $this->assertEquals(__FUNCTION__, ConfigTestLoader::$input);
        $this->assertEquals(['loader'=>'test', 'arg'=>1], ConfigTestLoader::$opts);
    }
    
    /**
     * Load from DSN
     * @covers Jasny\Config::load()
     * @depends testGetLoader
     */
    public function testLoad_DSN()
    {
        ConfigTestLoader::$data = (object)['a'=>'test'];
        
        $c = new Config();
        $c->load('test:' . __FUNCTION__, ['arg'=>1]);
        
        $this->assertEquals('test', $c->a);
        $this->assertEquals(__FUNCTION__, ConfigTestLoader::$input);
        $this->assertEquals(['loader'=>'test', 'arg'=>1], ConfigTestLoader::$opts);
    }
}

/**
 * Loader stub
 * @ignore
 */
class ConfigTestLoader extends Config\Loader
{
    static public $input;
    static public $opts;
    static public $data;

    public function __construct($options=array())
    {
        self::$opts = $options;
    }
    
    public function load($input)
    {
        self::$input = $input;
        return self::$data;
    }
    
    public static function reset()
    {
        self::$input = null;
        self::$opts = null;
        self::$data = null;
    }
}
