<?php

namespace Jasny\Config\Tests\Loader;

use Jasny\Config;
use Jasny\Config\Loader\YamlLoader;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @covers \Jasny\Config\Loader\YamlLoader
 * @covers \Jasny\Config\Loader\AbstractFileLoader
 */
class YamlLoaderTest extends TestCase
{
    /**
     * @var YamlLoader
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
        $this->loader = new YamlLoader();
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

    public function testLoadNum()
    {
        vfsStream::create(['test.yaml' => "---\nq: a---\nq: b"]);

        $result = $this->loader->load('vfs://root/test.yaml');

        $this->assertEquals(new Config(['q' => 'b']), $result);
    }

    public function testLoadCallbacks()
    {
        $callbacks = [
            '!mytag' => function ($value) {
                return str_replace('bar','baz', $value);
            }
        ];

        vfsStream::create(['test.yaml' => "q: !mytag some bar at x"]);

        $result = $this->loader->load('vfs://root/test.yaml', compact('callbacks'));

        $this->assertEquals(new Config(['q' => 'some baz at x']), $result);
    }

    public function testLoadOptions()
    {
        vfsStream::create(['test.yaml' => "---\nq: a---\nq: b"]);

        $loader = new YamlLoader(['base_path' => 'vfs://root/', 'num' => 2]);
        $this->assertSame(['base_path' => 'vfs://root/', 'num' => 2], $loader->getOptions());

        $result = $this->loader->load('vfs://root/test.yaml');

        $this->assertEquals(new Config(['q' => 'b']), $result);
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
     * @expectedExceptionMessage Failed to load settings from 'vfs://root/test.yaml': scanning error encountered during parsing: found character that cannot start any token
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
}
