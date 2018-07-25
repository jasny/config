<?php

namespace Jasny\Config\Tests\Loader;

use Jasny\Config;
use Jasny\Config\Loader\IniLoader;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @covers \Jasny\Config\Loader\IniLoader
 * @covers \Jasny\Config\Loader\AbstractFileLoader
 */
class IniLoaderTest extends TestCase
{
    /**
     * @var IniLoader
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
        $this->loader = new IniLoader();
        $this->root = vfsStream::setup();
    }


    public function testLoad()
    {
        $ini = <<<INI
q = ini
b = 27
a = foobar
INI;
        vfsStream::create(['test.ini' => $ini]);

        $expected = new Config(['q'=>'ini', 'b'=>27, 'a'=>'foobar']);

        $result = $this->loader->load('vfs://root/test.ini');

        $this->assertEquals($expected, $result);
    }

    public function sectionsProvider()
    {
        return [
            [false, ['q'=>'ini', 'b'=>27, 'a'=>'foobar']],
            [true, ['grp1' => (object)['q'=>'ini', 'b'=>27], 'grp2'=>(object)['a'=>'foobar']]]
        ];
    }

    /**
     * @dataProvider sectionsProvider
     */
    public function testLoadSections($process_sections, $expected)
    {
        $ini = <<<INI
[grp1]
q = ini
b = 27

[grp2]
a = foobar
INI;
        vfsStream::create(['test.ini' => $ini]);

        $result = $this->loader->load('vfs://root/test.ini', compact('process_sections'));

        $this->assertEquals(new Config($expected), $result);
    }

    public function modeProvider()
    {
        return [
            [INI_SCANNER_NORMAL, ['q' => true, 'b' => 27, 'a' => false]],
            [INI_SCANNER_RAW, ['q' => 'on', 'b' => '27', 'a' => 'off']]
        ];
    }

    /**
     * @dataProvider modeProvider
     */
    public function testLoadMode($mode, $expected)
    {
        $ini = <<<INI
q = on
b = 27
a = off
INI;
        vfsStream::create(['test.ini' => $ini]);

        $result = $this->loader->load('vfs://root/test.ini', ['mode' => $mode]);

        $this->assertEquals(new Config($expected), $result);
    }

    public function testLoadUnicode()
    {
        vfsStream::create(['test.ini' => 'q = 🙈🙈🙊']);

        $result = $this->loader->load('vfs://root/test.ini');

        $this->assertEquals(new Config(['q' => '🙈🙈🙊']), $result);
    }

    public function testLoadRelativePath()
    {
        vfsStream::create(['test.ini' => 'q = a']);

        $result = $this->loader->load('test.ini', ['base_path' => 'vfs://root/']);

        $this->assertEquals(new Config(['q' => 'a']), $result);
    }

    public function testLoadOptions()
    {
        vfsStream::create(['test.ini' => 'q = a']);

        $loader = new IniLoader(['base_path' => 'vfs://root/']);
        $this->assertSame(['base_path' => 'vfs://root/'], $loader->getOptions());

        $result = $this->loader->load('vfs://root/test.ini');

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
     * @expectedExceptionMessage Failed to load settings from 'vfs://root/test.ini'
     */
    public function testLoadFailed()
    {
        vfsStream::create(['test.ini' => '']);
        $this->loader->load('vfs://root/test.ini');
    }
}
