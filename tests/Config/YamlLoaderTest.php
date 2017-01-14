<?php

namespace Jasny\Config;

use Jasny\Config;
Use Jasny\Config\LoaderInterface;
use Jasny\Config\YamlLoader;
use org\bovigo\vfs\vfsStream;

/**
 * @covers Jasny\Config\YamlLoader
 */
class YamlLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var YamlLoader
     */
    protected $loader;
    
    /**
     * @var vfsStreamDirectory
     */
    protected $root;
    
    
    public function setUp()
    {
        $this->loader = new YamlLoader();
        $this->root = vfsStream::setup();
    }
    
    
    public function testGetLoader()
    {
        $use = $this->createMock(LoaderInterface::class);
        
        $loader = $this->loader->getLoader(compact('use'));
        
        $this->assertSame($use, $loader);
    }
    
    public function loaderProvider()
    {
        return [
            ['yaml', YamlYamlLoader::class],
            ['symfony', YamlSymfonyLoader::class],
            ['spyc', YamlSpycLoader::class]
        ];
    }
    
    /**
     * @dataProvider loaderProvider
     * 
     * @param string $use
     * @param string $class
     */
    public function testGetLoaderUse($use, $class)
    {
        $loader = $this->loader->getLoader(compact('use'));
        
        $this->assertInstanceof($class, $loader);
    }
    
    /**
     * @dataProvider loaderProvider
     * 
     * @param string $use
     * @param string $class
     */
    public function testGetLoaderGuess($use, $class)
    {
        $this->loader = $this->createPartialMock(YamlLoader::class, ['guessLoader']);
        $this->loader->method('guessLoader')->willReturn($use);
        
        $loader = $this->loader->getLoader([]);
        
        $this->assertInstanceof($class, $loader);
    }
    
    
    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage YamlNonExistingLoader does not exist
     */
    public function testGetLoaderWithNonExisting()
    {
        $this->loader->getLoader(['use' => 'non_existing']);
    }
    
    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage stdClass doesn't implement LoaderInterface
     */
    public function testGetLoaderWithInvalidClass()
    {
        $this->loader->getLoader(['use' => new \stdClass()]);
    }
    
    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage array doesn't implement LoaderInterface
     */
    public function testGetLoaderWithInvalidUse()
    {
        $this->loader->getLoader(['use' => []]);
    }
    
    
    public function testLoad()
    {
        $use = $this->createMock(LoaderInterface::class);
        $config = $this->createMock(Config::class);
        $options = ['use' => $use, 'foo' => 'bar'];
        
        vfsStream::create(['test.yml' => '']);
        $filename = vfsStream::url('root/test.yml');
        
        $use->expects($this->once())->method('load')->with($filename, $this->identicalTo($options))
            ->willReturn($config);
        
        $ret = $this->loader->load($filename, $options);
        
        $this->assertSame($config, $ret);
    }
}
