<?php
/**
 * Jasny Config - Configure your application.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/config/master/LICENSE MIT
 * @link    https://jasny.github.io/config
 */
/** */
namespace Jasny\Config;

use Jasny\Config;

/**
 * Test for Jasny\Config\DirLoader
 * 
 * @package Test
 */
class DirLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DirLoader;
     */
    protected $loader;
    
    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        DirTestLoader::$data = [
            'grp1' => (object)['q'=>'abc', 'b'=>27],
            'grp2' => (object)['a'=>'foobar'],
            'grp3' => array('one', 'two', 'three'),
            'section1/foo'=>'ABC',
            'section1/bar'=>'XYZ'
        ];
    }
    
    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        Config::$loaders['test'] = 'Jasny\Config\DirTestLoader';
        $this->loader = new DirLoader();
    }
    
    /**
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset(Config::$loaders['test']);
        $this->loader = null;
        
        DirTestLoader::reset();
    }
    
    
    /**
     * Test using test loader
     * @covers Jasny\Config\DirLoader::Load()
     */
    public function testLoad()
    {
        $data = (object)[
            'grp1' => (object)['q'=>'abc', 'b'=>27],
            'grp2' => (object)['a'=>'foobar'],
            'grp3' => array('one', 'two', 'three'),
            'section1'=>(object)['foo'=>'ABC', 'bar'=>'XYZ']
        ];
        
        $result = $this->loader->load(CONFIGTEST_SUPPORT_PATH . '/test');
        $this->assertEquals($data, $result);
    }

    /**
     * Test using any kind of Loader
     */
    public function testLoad_Any()
    {
        $data = (object)[
            'grp1' => (object)['q'=>'abc', 'b'=>27],
            'grp2' => (object)['a'=>'foobar'],
            'grp3' => ['one', 'two', 'three']
        ];
        
        $result = $this->loader->load(CONFIGTEST_SUPPORT_PATH . '/test-any');
        
        $this->assertEquals($data, $result);
    }

    /**
     * Test loading a non-existant file
     * @covers Jasny\Config\DirLoader::Load()
     */
    public function testLoad_Optional()
    {
        $this->loader->options['optional'] = true;
        $result = $this->loader->load(uniqid() . '.fake');
        
        $this->assertNull($result);
    }
}

/**
 * File loader stub
 * @ignore
 */
class DirTestLoader extends Loader
{
    static public $data;
    
    public function load($file)
    {
        $key = preg_replace('/^' . preg_quote(CONFIGTEST_SUPPORT_PATH . '/test/', '/') . '|\.test$/', '', $file);
        return self::$data[$key];
    }
    
    public static function reset()
    {
        self::$data = null;
    }
}
