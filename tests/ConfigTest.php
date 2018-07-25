<?php

namespace Jasny\Tests;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\Config\Loader\DelegateLoader;
use stdClass;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @covers \Jasny\Config
 */
class ConfigTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    protected $root;

    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->root = vfsStream::setup();
    }


    public function testConstruct()
    {
        $foo = 'bar';
        
        $colors = [
            'red' => 10,
            'blue' => 60,
            'green' => 100
        ];
        
        $animals = [
            'cow',
            'bunny',
            'duck'
        ];
        
        $config = new Config(compact('foo', 'colors', 'animals'));
        
        $this->assertInstanceOf(\stdClass::class, $config);
        $this->assertAttributeEquals('bar', 'foo', $config);
        $this->assertAttributeEquals((object)$colors, 'colors', $config);
        $this->assertAttributeEquals($animals, 'animals', $config);
    }
    
    /**
     * @expectedException TypeError
     */
    public function testConstructWithInvalidArgument()
    {
        new Config('foo');
    }

    public function testGetDefaultLoader()
    {
        $config = new Config();

        $loader = $config->getLoader();
        $this->assertInstanceOf(DelegateLoader::class, $loader);
    }
    
    public function testLoad()
    {
        $options = ['color' => 'blue'];

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->once())->method('load')->with('foo', $options)->willReturn(
            new Config(['foo' => 'bar'])
        );

        $config = new Config([], $loader);
        $this->assertSame($loader, $config->getLoader());

        $ret = $config->load('foo', $options);
        
        $this->assertSame($config, $ret);
        $this->assertAttributeEquals('bar', 'foo', $config);
    }
    
    /**
     * @expectedException TypeError
     */
    public function testLoadWithInvalidData()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->once())->method('load')->willReturn(['foo' => 'bar']);

        $config = new Config([], $loader);
        $config->load('foo');
    }
    
    
    public function testMerge()
    {
        $source = (object)['foo' => 10, 'bar' => 20, 'db' => (object)['host' => 'localhost', 'port' => 1234]];
        $add1 = (object)['bar' => 21, 'db' => (object)['host' => 'db.example.com', 'user' => 'foo', 'pass' => 'secret']];
        $add2 = (object)['bar' => 22, 'db' => (object)['user' => 'bar', 'ssl' => true], 'woo' => 100];

        $config = new Config($source);
        $ret = $config->merge($add1, $add2);
        
        $this->assertSame($config, $ret);
        
        $this->assertEquals(new Config([
            'foo' => 10,
            'bar' => 22,
            'db' => (object)[
                'host' => 'db.example.com',
                'port' => 1234,
                'user' => 'bar',
                'pass' => 'secret',
                'ssl' => true
            ],
            'woo' => 100
        ]), $config);
    }
    
    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 3 passed to Jasny\Config::merge() must be an instance of stdClass, string given
     */
    public function testMergeWithInvalidArgument()
    {
        (new Config())->merge(new stdClass(), new stdClass(), 'foo');
    }


    public function testSaveAsScript()
    {
        $source = (object)['foo' => 10, 'bar' => 20, 'db' => (object)['host' => 'localhost', 'port' => 1234]];
        $config = new Config($source);

        $config->saveAsScript('vfs://root/test.php');

        $this->assertFileExists('vfs://root/test.php');

        $ret = Config::loadFromScript('vfs://root/test.php');

        $this->assertEquals($config, $ret);
    }

    public function testLoadFromScriptNonExistent()
    {
        $ret = Config::loadFromScript('vfs://root/non-existent.php');

        $this->assertNull($ret);
    }

    public function testGet()
    {
        $config = new Config(['lvl1' => ['lvl2' => 'foo'], 'empty' => false]);

        $this->assertEquals((object)['lvl2' => 'foo'], $config->get('lvl1'));
        $this->assertEquals('foo', $config->get('lvl1.lvl2'));
        $this->assertFalse($config->get('empty'));
    }

    /**
     * @expectedException \Jasny\Config\Exception\NotFoundException
     * @expectedExceptionMessage Config setting 'nonexistent' not found
     */
    public function testGetNotFound()
    {
        $config = new Config([]);

        $config->get('nonexistent');
    }

    public function testHas()
    {
        $config = new Config(['lvl1' => [ 'lvl2' => 'foo'], 'empty' => false]);

        $this->assertTrue($config->has('lvl1'));
        $this->assertTrue($config->has('lvl1.lvl2'));
        $this->assertTrue($config->has('empty'));

        $this->assertFalse($config->has('nonexistent'));
        $this->assertFalse($config->has('lvl1.abc'));
    }
}
