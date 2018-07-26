<?php

namespace Jasny\Config\Tests\Loader;

use Jasny\Config;
use Jasny\Config\Loader\YamlSymfonyLoader;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @covers \Jasny\Config\Loader\YamlSymfonyLoader
 * @covers \Jasny\Config\Loader\AbstractFileLoader
 */
class YamlSymfonyLoaderTest extends TestCase
{
    /**
     * @var YamlSymfonyLoader
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
        $this->loader = new YamlSymfonyLoader();
        $this->root = vfsStream::setup();
    }


    public function testLoad()
    {
        $yaml = <<<YAML
grp1:
  q: yaml
  b: 27
grp2:
  a: foobar
grp3:
  - one
  - two
  - three
YAML;

        vfsStream::create(['test.yaml' => $yaml]);

        $expected = new Config([
            'grp1' => ['q' => 'yaml', 'b' => 27],
            'grp2' => ['a' => 'foobar'],
            'grp3' => ['one', 'two', 'three']
        ]);

        $result = $this->loader->load('vfs://root/test.yaml');

        $this->assertEquals($expected, $result);
    }

    public function testLoadUnicode()
    {
        vfsStream::create(['test.yaml' => 'q: 🙈🙈🙊']);

        $result = $this->loader->load('vfs://root/test.yaml');

        $this->assertEquals(new Config(['q' => '🙈🙈🙊']), $result);
    }

    public function testLoadRelativePath()
    {
        vfsStream::create(['test.yaml' => 'q: a']);

        $result = $this->loader->load('test.yaml', ['base_path' => 'vfs://root/']);

        $this->assertEquals(new Config(['q' => 'a']), $result);
    }


    public function testLoadOptions()
    {
        vfsStream::create(['test.yaml' => 'q: a']);

        $loader = new YamlSymfonyLoader(['base_path' => 'vfs://root/']);
        $this->assertSame(['base_path' => 'vfs://root/'], $loader->getOptions());

        $result = $this->loader->load('vfs://root/test.yaml');

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
     * @expectedExceptionMessage Failed to load settings from 'vfs://root/test.yaml'
     */
    public function testLoadFailed()
    {
        vfsStream::create(['test.yaml' => '']);
        $this->loader->load('vfs://root/test.yaml');
    }

    /**
     * @expectedException \Jasny\Config\Exception\LoadException
     * @expectedExceptionMessage Failed to load settings from 'vfs://root/test.yaml': A YAML file cannot contain tabs as indentation
     */
    public function testLoadSyntaxError()
    {
        vfsStream::create(['test.yaml' => "grp1:\n\tq: a"]);
        $this->loader->load('vfs://root/test.yaml');
    }

    /**
     * @expectedException \Jasny\Config\Exception\LoadException
     * @expectedExceptionMessage Failed to load settings from 'vfs://root/test.yaml': data should be key/value pairs
     */
    public function testLoadUnexpectedValue()
    {
        vfsStream::create(['test.yaml' => '"hello"']);
        $this->loader->load('vfs://root/test.yaml');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Options 'num' and 'callbacks' aren't supported with the Symfony YAML parser. Please install the yaml PHP extension.
     */
    public function testLoadNumOption()
    {
        vfsStream::create(['test.yaml' => 'q: a']);
        $this->loader->load('vfs://root/test.yaml', ['num' => 2]);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Options 'num' and 'callbacks' aren't supported with the Symfony YAML parser. Please install the yaml PHP extension.
     */
    public function testLoadCallbacksOption()
    {
        vfsStream::create(['test.yaml' => 'q: a']);
        $this->loader->load('vfs://root/test.yaml', ['callbacks' => [ function() {} ]]);
    }
}
