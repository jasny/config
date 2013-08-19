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

/**
 * Tests for Jasny\Config\JsonLoader.
 * 
 * @package Test
 */
class JsonLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected static $json = <<<JSON
{
  "grp1" : {
    "q" : "json",
    "b" : 27
  },
  
  "grp2" : {
    "a" : "foobar"
  },
  
  "grp3" : [
      "one",
      "two",
      "three"
  ]
}
JSON;
    
    /**
     * @var JsonLoader
     */
    protected $loader;
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        if (!function_exists('json_decode')) $this->markTestSkipped("json php extension not loaded");
        
        $this->loader = new JsonLoader();
    }

    /**
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->loader = null;
    }

    
    /**
     * Get test config data
     * 
     * @return object
     */
    protected function getTestData()
    {
        return (object)[
            'grp1'=>(object)['q'=>'json', 'b'=>27],
            'grp2'=>(object)['a'=>'foobar'],
            'grp3'=>['one', 'two', 'three']
        ];
    }
    
    
    /**
     * Test parsing a string
     */
    public function testParse()
    {
        $result = $this->loader->parse(static::$json);
        $this->assertEquals($this->getTestData(), $result);
    }
    
    /**
     * Test loading a file
     */
    public function testLoadFile()
    {
        $result = $this->loader->load(CONFIGTEST_SUPPORT_PATH . '/test.json');
        $this->assertEquals($this->getTestData(), $result);
    }
    
    /**
     * Test loading a dir
     */
    public function testLoadDir()
    {
        $data = (object)['grp3'=>['one', 'two', 'three']];
        
        $result = $this->loader->load(CONFIGTEST_SUPPORT_PATH . '/test-any');
        $this->assertEquals($data, $result);
    }
}
