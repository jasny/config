<?php

namespace Jasny\Config\Tests\Loader;

use Jasny\Config;
use Jasny\Config\Loader;
use Jasny\Config\Loader\DelegateLoader;
use Jasny\Config\LoaderInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

/**
 * @covers \Jasny\Config\Loader\DelegateLoader
 */
class DelegateLoaderTest extends TestCase
{
    /**
     * @var DelegateLoader
     */
    protected $loader;

    /**
     * @var LoaderInterface[]|MockObject[]
     */
    protected $loaders;

    public function setUp()
    {
        $this->loaders = [
            'foo' => $this->createMock(LoaderInterface::class),
            'bar' => $this->createMock(LoaderInterface::class),
            'stdClass' => $this->createMock(LoaderInterface::class)
        ];

        $this->loader = new DelegateLoader($this->loaders);
    }

    public function testLoadFile()
    {
        $config = $this->createMock(Config::class);
        $options = ['a' => 'b'];

        $this->loaders['foo']->expects($this->never())->method('load');
        $this->loaders['stdClass']->expects($this->never())->method('load');

        $this->loaders['bar']->expects($this->once())->method('load')->with('test.bar', $options)
            ->willReturn($config);

        $result = $this->loader->load('test.bar', $options);

        $this->assertSame($config, $result);
    }

    public function testLoadSource()
    {
        $config = $this->createMock(Config::class);
        $options = ['a' => 'b'];

        $this->loaders['foo']->expects($this->never())->method('load');
        $this->loaders['stdClass']->expects($this->never())->method('load');

        $this->loaders['bar']->expects($this->once())->method('load')->with('bar', $options)
            ->willReturn($config);

        $result = $this->loader->load('bar', $options);

        $this->assertSame($config, $result);
    }

    public function testLoadObject()
    {
        $input = new stdClass();
        $config = $this->createMock(Config::class);
        $options = ['a' => 'b'];

        $this->loaders['foo']->expects($this->never())->method('load');
        $this->loaders['bar']->expects($this->never())->method('load');

        $this->loaders['stdClass']->expects($this->once())->method('load')
            ->with($this->identicalTo($input), $options)
            ->willReturn($config);

        $result = $this->loader->load($input, $options);

        $this->assertSame($config, $result);
    }

    /**
     * @expectedException \Jasny\Config\Exception\NoLoaderException
     * @expectedExceptionMessage Don't know how to load configuration from 'test.yaml'
     */
    public function testLoadFileNoLoader()
    {
        $this->loaders['foo']->expects($this->never())->method('load');
        $this->loaders['bar']->expects($this->never())->method('load');
        $this->loaders['stdClass']->expects($this->never())->method('load');

        $this->loader->load('test.yaml');
    }

    /**
     * @expectedException \Jasny\Config\Exception\NoLoaderException
     * @expectedExceptionMessage Don't know how to load configuration from DateTime object
     */
    public function testLoadObjectNoLoader()
    {
        $this->loaders['foo']->expects($this->never())->method('load');
        $this->loaders['bar']->expects($this->never())->method('load');
        $this->loaders['stdClass']->expects($this->never())->method('load');

        $this->loader->load(new \DateTime());
    }

    /**
     * @expectedException \TypeError
     */
    public function testLoadTypeError()
    {
        $this->loader->load([]);
    }


    public function testDefaultLoaders()
    {
        $loaders = DelegateLoader::getDefaultLoaders();

        $this->assertArrayHasKey('dir', $loaders);
        $this->assertInstanceOf(Loader\DirLoader::class, $loaders['dir']);
        $this->assertArrayHasKey('ini', $loaders);
        $this->assertInstanceOf(Loader\IniLoader::class, $loaders['ini']);
        $this->assertArrayHasKey('json', $loaders);
        $this->assertInstanceOf(Loader\JsonLoader::class, $loaders['json']);
        $this->assertArrayHasKey('yaml', $loaders);
        $this->assertInstanceOf(Loader\YamlLoader::class, $loaders['yaml']);
        $this->assertArrayHasKey('yml', $loaders);
        $this->assertInstanceOf(Loader\YamlLoader::class, $loaders['yml']);
        $this->assertArrayHasKey('env', $loaders);
        $this->assertInstanceOf(Loader\EnvLoader::class, $loaders['env']);
        $this->assertArrayHasKey('Aws\DynamoDb\DynamoDbClient', $loaders);
        $this->assertInstanceOf(Loader\DynamoDBLoader::class, $loaders['Aws\DynamoDb\DynamoDbClient']);

        $loader = new DelegateLoader();

        $loaders['dir']->setFileLoader($loader);

        $this->assertAttributeEquals($loaders, 'loaders', $loader);
    }
}
