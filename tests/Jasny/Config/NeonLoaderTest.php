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
 * Tests for Loader.
 * 
 * @package Test
 */
class NeonLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NeonLoader
     */
    protected $loader;
    
    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->loader = new NeonLoader();
    }

    /**
     * Test parsing a string
     */
    public function testParse()
    {
        $this->markTestIncomplete("Test for NeonLoader::parse() is not written yet");
    }
    
    /**
     * Test loading a file
     */
    public function testLoadFile()
    {
        $this->markTestIncomplete("Test for NeonLoader::loadFile() is not written yet");
    }
    
    /**
     * Test loading a dir
     */
    public function testLoadDir()
    {
        $this->markTestIncomplete("Test for NeonLoader::loadDir() is not written yet");
    }
}
