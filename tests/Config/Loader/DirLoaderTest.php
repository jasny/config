<?php

namespace Jasny\Config\Tests\Loader;

use Jasny\Config;
use Jasny\Config\Loader\DirLoader;
use Jasny\Config\LoaderInterface;
use Jasny\Config\Loader\DelegateLoader;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @covers \Jasny\Config\Loader\DirLoader
 */
class DirLoaderTest extends TestCase
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


    public function testGetDefaultLoader()
    {
        $loader = $this->loader->getFileLoader();
        $this->assertInstanceOf(DelegateLoader::class, $loader);
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
        
        $fileLoader = $this->createMock(LoaderInterface::class);
        $options = ['color' => 'blue'];

        $fileLoader->expects($this->at(0))->method('load')->with('vfs://root/ape', $options)->willReturn($ape);
        $fileLoader->expects($this->at(1))->method('load')->with('vfs://root/bear', $options)->willReturn($bear);
        $fileLoader->expects($this->at(2))->method('load')->with('vfs://root/cat.ini', $options)->willReturn($cat);

        $this->loader->setFileLoader($fileLoader);

        $result = $this->loader->load('vfs://root/', $options);
        
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
        
        $fileLoader = $this->createMock(LoaderInterface::class);
        $options = ['color' => 'blue'];
        
        $fileLoader->expects($this->at(0))->method('load')->with('vfs://root/ape.ini', $options)
            ->willReturn($ape1);
        $fileLoader->expects($this->at(1))->method('load')->with('vfs://root/ape.json', $options)
            ->willReturn($ape2);

        $this->loader->setFileLoader($fileLoader);

        $result = $this->loader->load('vfs://root/', $options);
        
        $this->assertInstanceOf(Config::class, $result);
        $this->assertEquals(new Config([
            'ape' => new Config([
                'q' => 'abcdef',
                'b' => 27,
                'c' => 99
            ])
        ]), $result);
    }
    
    public function testLoadRecursive()
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
        
        $fileLoader = $this->createMock(LoaderInterface::class);
        $options = ['recursive' => true];
        
        $fileLoader->expects($this->at(0))->method('load')->with('vfs://root/animal/ape/bonabo', $options)
            ->willReturn($bonabo);
        $fileLoader->expects($this->at(1))->method('load')->with('vfs://root/animal/ape/gorilla', $options)
            ->willReturn($gorilla);
        $fileLoader->expects($this->at(2))->method('load')->with('vfs://root/animal/bear', $options)
            ->willReturn($bear);

        $this->loader->setFileLoader($fileLoader);

        $result = $this->loader->load('vfs://root/', $options);
        
        $this->assertInstanceOf(Config::class, $result);
        $this->assertEquals(new Config([
            'animal' => new Config([
                'ape' => new Config(compact('bonabo', 'gorilla')),
                'bear' => $bear
            ])
        ]), $result);
    }


    public function testLoadNonRecursive()
    {
        $zoo = new Config(['foo' => 'qux']);

        vfsStream::create([
            'ape' => [
                'gorilla' => '',
                'bonabo' => '',
            ],
            'zoo' => ''
        ]);

        $fileLoader = $this->createMock(LoaderInterface::class);
        $options = ['recursive' => false];

        $fileLoader->expects($this->once())->method('load')->with('vfs://root/zoo', $options)
            ->willReturn($zoo);

        $this->loader->setFileLoader($fileLoader);

        $result = $this->loader->load('vfs://root/', $options);

        $this->assertInstanceOf(Config::class, $result);
        $this->assertEquals(new Config(['zoo' => $zoo]), $result);
    }

    /**
     * @expectedException \Jasny\Config\Exception\LoadException
     * @expectedExceptionMessage Config directory 'vfs://unknown' doesn't exist
     */
    public function testLoadNotFound()
    {
        $fileLoader = $this->createMock(LoaderInterface::class);
        $this->loader->setFileLoader($fileLoader);

        $this->loader->load(vfsStream::url('unknown'));
    }

    public function testLoadOptional()
    {
        $fileLoader = $this->createMock(LoaderInterface::class);
        $this->loader->setFileLoader($fileLoader);

        $result = $this->loader->load(vfsStream::url('unknown'), ['optional' => true]);
        
        $this->assertInstanceOf(Config::class, $result);
        $this->assertEquals(new Config(), $result);
    }
    
    public function testLoadWithStringableObject()
    {
        $ape = new Config(['q' => 'abc', 'b' => 27]);
        
        vfsStream::create([
            'ape' => '',
        ]);
        
        $fileLoader = $this->createMock(LoaderInterface::class);

        $fileLoader->expects($this->at(0))->method('load')->with('vfs://root/ape')->willReturn($ape);
        
        $dir = $this->createPartialMock('stdClass', ['__toString']);
        $dir->method('__toString')->willReturn(vfsStream::url('root'));

        $this->loader->setFileLoader($fileLoader);

        $result = $this->loader->load($dir);
        
        $this->assertInstanceOf(Config::class, $result);
        $this->assertEquals(new Config(compact('ape')), $result);
    }
    
    /**
     * @expectedException \TypeError
     * @expectedExceptionMessage Expected a string as directory, got a array
     */
    public function testLoadWithInvalidArgument()
    {
        $this->loader->load(['foo' => 'bar']);
    }
}
