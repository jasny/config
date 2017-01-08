<?php

namespace Jasny;

use Jasny\Config;
use Jasny\Config\LoaderInterface;

/**
 * @covers Jasny\Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Settings should be an array or stdClass object, not a string. Use the `load` method to load settings from a source.
     */
    public function testConstructWithInvalidArgument()
    {
        new Config('foo');
    }
    
    
    public function testLoad()
    {
        $config = new Config();
        
        $loader = $this->createMock(LoaderInterface::class);
        $options = ['loader' => $loader, 'arg' => 1];
        
        $loader->expects($this->once())->method('load')->with('foo', $options)->willReturn(
            new Config(['foo' => 'bar'])
        );
        
        $ret = $config->load('foo', $options);
        
        $this->assertSame($config, $ret);
        $this->assertAttributeEquals('bar', 'foo', $config);
    }
    
    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Loader returned a array instead of a Config object
     */
    public function testLoadWithInvalidData()
    {
        $config = new Config();
        
        $loader = $this->createMock(LoaderInterface::class);
        $options = ['loader' => $loader];
        
        $loader->expects($this->once())->method('load')->willReturn(['foo' => 'bar']);
        
        $config->load('foo', $options);
    }
    
    
    public function testMerge()
    {
        $target = (object)['foo' => 10, 'bar' => 20, 'db' => (object)['host' => 'localhost', 'port' => 1234]];
        $add1 = (object)['bar' => 21, 'db' => (object)['host' => 'db.example.com', 'user' => 'foo', 'pass' => 'secret']];
        $add2 = (object)['bar' => 22, 'db' => (object)['user' => 'bar', 'ssl' => true]];
        
        $ret = Config::merge($target, $add1, $add2);
        
        $this->assertSame($target, $ret);
        
        $this->assertEquals((object)[
            'foo' => 10,
            'bar' => 22,
            'db' => (object)[
                'host' => 'db.example.com',
                'port' => 1234,
                'user' => 'bar',
                'pass' => 'secret',
                'ssl' => true
            ]
        ], $target);
    }
    
    public function testMergeWithNull()
    {
        $target = (object)['foo' => 10];
        $add2 = (object)['bar' => 20];
        
        $ret = Config::merge($target, null, $add2);
        
        $this->assertSame($ret, $target);
        
        $this->assertEquals((object)[
            'foo' => 10,
            'bar' => 20
        ], $target);
    }
    
    public function testMergeAddProperty()
    {
        $object = (object)['foo' => 10];
        $db = (object)['host' => 'localhost', 'port' => 1234];
        
        $ret = Config::merge($object->db, $db);
        
        $this->assertSame($ret, $db);
        $this->assertAttributeSame($db, 'db', $object);
        
        $this->assertEquals((object)[
            'foo' => 10,
            'db' => (object)['host' => 'localhost', 'port' => 1234]
        ], $object);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument 3 is not a stdClass object, but a string
     */
    public function testMergeWithInvalidArgument()
    {
        Config::merge(new \stdClass(), new \stdClass(), 'foo');
    }
}
