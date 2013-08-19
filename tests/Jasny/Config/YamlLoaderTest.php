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
 * Tests for Jasny\Config\YamlLoader.
 * 
 * @package Test
 */
class YamlLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected static $yaml = <<<YAML
grp1:
   q : yaml
   b : 27
grp2:
   a : foobar
grp3:
   - one
   - two
   - three
YAML;
    
    /**
     * @var YamlLoader
     */
    protected $loader;
    
    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->loader = new YamlLoader();
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
            'grp1'=>(object)['q'=>'yaml', 'b'=>27],
            'grp2'=>(object)['a'=>'foobar'],
            'grp3'=>['one', 'two', 'three']
        ];
    }
    
    
    /**
     * Test parsing a string
     */
    public function testParse()
    {
        $result = $this->loader->parse(self::$yaml);
        $this->assertEquals($this->getTestData(), $result);
    }
    
    /**
     * Test parsing a string using Spyc
     */
    public function testParse_Spyc()
    {
        $this->loader = new YamlLoader(['use'=>'spyc']);
        $result = $this->loader->parse(self::$yaml);
        $this->assertEquals($this->getTestData(), $result);
    }
    
    /**
     * Test loading a file
     */
    public function testLoadFile()
    {
        $result = $this->loader->load(CONFIGTEST_SUPPORT_PATH . '/test.yaml');
        $this->assertEquals($this->getTestData(), $result);
    }
}
