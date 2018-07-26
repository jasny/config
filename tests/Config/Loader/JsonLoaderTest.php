<?php

namespace Jasny\Config\Tests\Loader;

use Jasny\Config;
use Jasny\Config\Loader\JsonLoader;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @covers \Jasny\Config\Loader\JsonLoader
 * @covers \Jasny\Config\Loader\AbstractFileLoader
 */
class JsonLoaderTest extends TestCase
{
    /**
     * @var JsonLoader
     */
    protected $loader;

    /**
     * @var vfsStreamDirectory
     */
    protected $root;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->loader = new JsonLoader();
        $this->root = vfsStream::setup();
    }


    public function testLoad()
    {
        $json = <<<JSON
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

        vfsStream::create(['test.json' => $json]);

        $expected = new Config([
            'grp1' => ['q' => 'json', 'b' => 27],
            'grp2' => ['a' => 'foobar'],
            'grp3' => ['one', 'two', 'three']
        ]);

        $result = $this->loader->load('vfs://root/test.json');

        $this->assertEquals($expected, $result);
    }


    public function testLoadUnicode()
    {
        vfsStream::create(['test.json' => '{ "q": "🙈🙈🙊" }']);

        $result = $this->loader->load('vfs://root/test.json');

        $this->assertEquals(new Config(['q' => '🙈🙈🙊']), $result);
    }

    public function testLoadRelativePath()
    {
        vfsStream::create(['test.json' => '{ "q": "a" }']);

        $result = $this->loader->load('test.json', ['base_path' => 'vfs://root/']);

        $this->assertEquals(new Config(['q' => 'a']), $result);
    }

    public function testLoadOptions()
    {
        vfsStream::create(['test.json' => '{ "q": "a" }']);

        $loader = new JsonLoader(['base_path' => 'vfs://root/']);
        $this->assertSame(['base_path' => 'vfs://root/'], $loader->getOptions());

        $result = $this->loader->load('vfs://root/test.json');

        $this->assertEquals(new Config(['q' => 'a']), $result);
    }

    /**
     * @expectedException \Jasny\Config\Exception\LoadException
     * @expectedExceptionMessage Config file 'vfs://unknown' doesn't exist or is not readable
     */
    public function testLoadNotFound()
    {
        $this->loader->load(vfsStream::url('unknown'));
    }

    public function testLoadOptional()
    {
        $result = $this->loader->load(vfsStream::url('unknown'), ['optional' => true]);

        $this->assertInstanceOf(Config::class, $result);
        $this->assertEquals(new Config(), $result);
    }

    /**
     * @expectedException \Jasny\Config\Exception\LoadException
     * @expectedExceptionMessage Failed to load settings from 'vfs://root/test.json': Syntax error
     */
    public function testLoadFailed()
    {
        vfsStream::create(['test.json' => '']);
        $this->loader->load('vfs://root/test.json');
    }

    /**
     * @expectedException \Jasny\Config\Exception\LoadException
     * @expectedExceptionMessage Failed to load settings from 'vfs://root/test.json': Data should be an object
     */
    public function testLoadUnexpectedValue()
    {
        vfsStream::create(['test.json' => '"hello"']);
        $this->loader->load('vfs://root/test.json');
    }
}
