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
 * Tests for Jasny\Config\IniLoader.
 * 
 * @package Test
 */
class IniLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IniLoader
     */
    protected $loader;
    
    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->loader = new IniLoader();
    }

    /**
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->loader = null;
    }
    
    
    /**
     * Test parsing a string
     */
    public function testParse()
    {
        $ini = <<<INI
[grp1]
q = ini
b = 27

[grp2]
a = foobar
INI;
        $data = (object)['grp1' => (object)['q'=>'ini', 'b'=>27], 'grp2'=>(object)['a'=>'foobar']];
        
        $result = $this->loader->parse($ini);
        $this->assertEquals($data, $result);
    }
    
    /**
     * Test loading a file
     */
    public function testLoadFile()
    {
        $data = (object)['grp1' => (object)['q'=>'ini', 'b'=>27], 'grp2'=>(object)['a'=>'foobar']];
        
        $result = $this->loader->load(CONFIGTEST_SUPPORT_PATH . '/test.ini');
        $this->assertEquals($data, $result);
    }
    
    /**
     * Test loading a dir
     */
    public function testLoadDir()
    {
        $data = (object)['grp1'=>(object)['q'=>'abc', 'b'=>27], 'grp2'=>(object)['a'=>'foobar']];
        
        $result = $this->loader->load(CONFIGTEST_SUPPORT_PATH . '/test-any');
        $this->assertEquals($data, $result);
    }
}
