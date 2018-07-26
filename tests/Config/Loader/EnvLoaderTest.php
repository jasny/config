<?php


namespace Config\Loader;

use Jasny\Config\Loader\EnvLoader;
use Jasny\Config;
use PHPUnit\Framework\TestCase;

class EnvLoaderTest extends TestCase
{
    /**
     * @var EnvLoader
     */
    protected $loader;

    public function setUp()
    {
        $this->loader = new EnvLoader();
    }

    public function testLoad()
    {
        $map = [
            'TEST_FOO' => 'foo',
            'TEST_BAR' => 'bar.color',
            'TEST_CUZ' => 'cuz'
        ];

        putenv("TEST_FOO=10");
        putenv("TEST_BAR=red");

        $config = $this->loader->load('env', compact('map'));

        $expected = new Config([
            'foo' => 10,
            'bar' => [
                'color' => 'red'
            ]
        ]);

        $this->assertEquals($expected, $config);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No 'map' option specified
     */
    public function testLoadNoMap()
    {
        $this->loader->load('env');
    }
}