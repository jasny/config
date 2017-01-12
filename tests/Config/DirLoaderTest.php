<?php

namespace Jasny\Config;

use Jasny\Config;
use Jasny\Config\DirLoader;
use Jasny\Config\LoaderInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @covers Jasny\Config\DirLoader
 */
class DirLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DirLoader
     */
    protected $loader;
    
    /**
     * @var vfsStreamDirectory
     */
    protected $root;
    
    
    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->loader = new DirLoader();
        $this->root = vfsStream::setup();
    }
    
    
    public function testLoad()
    {
        $ape = new Config(['q' => 'abc', 'b' => 27]);
        $bear = new Config(['a' => 'foobar']);
        $cat = new Config(['c' => 99, 'q' => 'abcdef']);
        
        vfsStream::create([
            'ape' => '',
            'bear' => '',
            'cat.ini' => ''
        ]);
        
        $delegateLoader = $this->createMock(LoaderInterface::class);
        $options = ['delegate_loader' => $delegateLoader];
        
        $delegateLoader->expects($this->at(0))->method('load')->with('vfs://root/ape', $options)->willReturn($ape);
        $delegateLoader->expects($this->at(1))->method('load')->with('vfs://root/bear', $options)->willReturn($bear);
        $delegateLoader->expects($this->at(2))->method('load')->with('vfs://root/cat.ini', $options)->willReturn($cat);
        
        $result = $this->loader->load(vfsStream::url('root'), ['delegate_loader' => $delegateLoader]);
        
        $this->assertInstanceOf(Config::class, $result);
        $this->assertEquals(new Config(compact('ape', 'bear', 'cat')), $result);
    }
    
    public function testLoadWithDuplicateKey()
    {
        $ape1 = new Config(['q' => 'abc', 'b' => 27]);
        $ape2 = new Config(['c' => 99, 'q' => 'abcdef']);
        
        vfsStream::create([
            'ape.ini' => '',
            'ape.json' => ''
        ]);
        
        $delegateLoader = $this->createMock(LoaderInterface::class);
        $options = ['delegate_loader' => $delegateLoader];
        
        $delegateLoader->expects($this->at(0))->method('load')->with('vfs://root/ape.ini', $options)
            ->willReturn($ape1);
        $delegateLoader->expects($this->at(1))->method('load')->with('vfs://root/ape.json', $options)
            ->willReturn($ape2);
        
        $result = $this->loader->load(vfsStream::url('root'), ['delegate_loader' => $delegateLoader]);
        
        $this->assertInstanceOf(Config::class, $result);
        $this->assertEquals(new Config([
            'ape' => new Config([
                'q' => 'abcdef',
                'b' => 27,
                'c' => 99
            ])
        ]), $result);
    }
    
    public function testLoadWithSubdirs()
    {
        $bonabo = new Config(['zoo' => 'qux']);
        $gorilla = new Config(['foo' => 'bar']);
        $bear = new Config(['q' => 'abc']);
        
        vfsStream::create([
            'animal' => [
                'ape' => [
                    'gorilla' => '',
                    'bonabo' => '',
                ],
                'bear' => ''
            ]
        ]);
        
        $delegateLoader = $this->createMock(LoaderInterface::class);
        $options = ['delegate_loader' => $delegateLoader];
        
        $delegateLoader->expects($this->at(0))->method('load')->with('vfs://root/animal/ape/bonabo', $options)
            ->willReturn($bonabo);
        $delegateLoader->expects($this->at(1))->method('load')->with('vfs://root/animal/ape/gorilla', $options)
            ->willReturn($gorilla);
        $delegateLoader->expects($this->at(2))->method('load')->with('vfs://root/animal/bear', $options)
            ->willReturn($bear);
        
        $result = $this->loader->load(vfsStream::url('root'), ['delegate_loader' => $delegateLoader]);
        
        $this->assertInstanceOf(Config::class, $result);
        $this->assertEquals(new Config([
            'animal' => new Config([
                'ape' => new Config(compact('bonabo', 'gorilla')),
                'bear' => $bear
            ])
        ]), $result);
    }

    /**
     * @expectedException Jasny\ConfigException
     * @expectedExceptionMessage Config directory 'vfs://unknown' doesn't exist
     */
    public function testLoadWithUnknownDir()
    {
        $this->loader->load(vfsStream::url('unknown'));
    }

    public function testLoadWithUnknownOptionalDir()
    {
        $result = $this->loader->load(vfsStream::url('unknown'), ['optional' => true]);
        
        $this->assertNull($result);
    }
    
    public function testLoadWithStringableObject()
    {
        $ape = new Config(['q' => 'abc', 'b' => 27]);
        
        vfsStream::create([
            'ape' => '',
        ]);
        
        $delegateLoader = $this->createMock(LoaderInterface::class);
        $options = ['delegate_loader' => $delegateLoader];
        
        $delegateLoader->expects($this->at(0))->method('load')->with('vfs://root/ape', $options)->willReturn($ape);
        
        $dir = $this->createPartialMock('stdClass', ['__toString']);
        $dir->method('__toString')->willReturn(vfsStream::url('root'));
        
        $result = $this->loader->load($dir, ['delegate_loader' => $delegateLoader]);
        
        $this->assertInstanceOf(Config::class, $result);
        $this->assertEquals(new Config(compact('ape')), $result);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected a string as directory, got a array
     */
    public function testLoadWithInvalidArgument()
    {
        $this->loader->load(['foo' => 'bar']);
    }
}
