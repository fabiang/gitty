<?php
namespace Gitty\Tests\Gitty;

use \Gitty as Gitty;

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/TestCase.php';

require_once \dirname(__FILE__).'/../../library/Gitty/Exception.php';
require_once \dirname(__FILE__).'/../../library/Gitty/Loader.php';

/**
 * @outputBuffering disabled
 */
class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Gitty\Loader::loadClass
     */
    public function testClassExists()
    {
        $return = Gitty\Loader::loadClass(__CLASS__);
        $this->assertEquals($return, null);
    }

    /**
     * @expectedException Gitty\Exception
     * @covers Gitty\Loader::loadClass
     */
    public function testSearchDirsException()
    {
        Gitty\Loader::loadClass('\\Inexistant___', 0);
    }

    /**
     * @covers Gitty\Loader::loadClass
     * @covers Gitty\Loader::loadFile
     */
    public function testSearchDirsTypes()
    {
        $dir = \dirname(__FILE__).'/../data/';
        Gitty\Loader::loadClass('ExampleNamespace\\Loader\\Class1', $dir);
        Gitty\Loader::loadClass('ExampleNamespace\\Loader\\Class2', array($dir));
    }

    /**
     * @expectedException Gitty\Exception
     * @covers Gitty\Loader::loadClass
     */
    public function testSearchDirsInexistantString()
    {
        Gitty\Loader::loadClass('ExampleNamespace\\Loader\\Class3', 'not_existant');
    }

    /**
     * @expectedException Gitty\Exception
     * @covers Gitty\Loader::loadClass
     */
    public function testSearchDirsInexistantArray()
    {
        Gitty\Loader::loadClass('ExampleNamespace\\Loader\\Class4', array('not_existant'));
    }

    /**
     * @covers Gitty\Loader::loadClass
     * @covers Gitty\Loader::securityCheck
     */
    public function testIncludePath()
    {
        $inc_path = \get_include_path();
        \set_include_path(\dirname(__FILE__).'/../data');
        Gitty\Loader::loadClass('ExampleNamespace\\Loader\\Class5');
        \set_include_path($inc_path);
    }

    /**
     * @covers Gitty\Loader::loadFile
     */
    public function testLoadFile()
    {
        $dir = \dirname(__FILE__).'/../data/ExampleNamespace/Loader/';
        Gitty\Loader::loadFile('Class6.php', $dir, true);

        $inc_path = \get_include_path();
        \set_include_path(\dirname(__FILE__).'/../data/ExampleNamespace/Loader/');
        Gitty\Loader::loadFile('Class7.php', null, true);
        \set_include_path($inc_path);
    }

    /**
     * @expectedException Gitty\Exception
     * @covers Gitty\Loader::securityCheck
     */
    public function testSecurityCheckInvalid()
    {
        $inc_path = \get_include_path();
        \set_include_path(\dirname(__FILE__).'/../data/');
        Gitty\Loader::loadClass('$%invalid.-filename/()');
        \set_include_path($inc_path);
    }

    /**
     * @covers Gitty\Loader::registerAutoload
     * @covers Gitty\Loader::autoload
     */
    public function testAutoLoad()
    {
        $inc_path = \get_include_path();
        \set_include_path(\dirname(__FILE__).'/../data/');
        Gitty\Loader::registerAutoload();

        $loaded = new \ExampleNamespace\Loader\Class8();

        Gitty\Loader::registerAutoload(null, false);
        \set_include_path($inc_path);

        $this->assertEquals('ExampleNamespace\\Loader\\Class8', Gitty\Loader::autoLoad('ExampleNamespace\\Loader\\Class8'));
        $this->assertFalse(Gitty\Loader::autoLoad('\Gitty\fsdfsfsdf'));
    }

    /**
     * @covers Gitty\Loader::__construct
     */
    public function testAutoloadConstruction()
    {
        $inc_path = \get_include_path();
        \set_include_path(\dirname(__FILE__).'/../data');

        $loader = new Gitty\Loader();
        $loaded = new \ExampleNamespace\Loader\Class9();

        Gitty\Loader::registerAutoload(null, false);
        \set_include_path($inc_path);
    }

    /**
     * @covers Gitty\Loader::loadClass
     */
    public function testSlashPrefix()
    {
        Gitty\Loader::loadClass('\\ExampleNamespace\\Loader\\Class10', \dirname(__FILE__).'/../data');
    }

    /**
     * @covers Gitty\Loader::loadClass
     */
    public function testDotDir()
    {
        Gitty\Loader::loadClass('ExampleNamespace\\Loader\\Class11', array('.', \dirname(__FILE__).'/../data'));
    }

    /**
     * @outputBuffering enabled
     * @covers Gitty\Loader::loadFile
     */
    public function testMultipleInclude()
    {
        Gitty\Loader::loadFile('include.php', \dirname(__FILE__).'/../data', false);
        Gitty\Loader::loadFile('include.php', \dirname(__FILE__).'/../data', false);
    }

    /**
     * @expectedException Gitty\Exception
     * @covers Gitty\Loader::registerAutoload
     */
    public function testInvalidLoaderClass()
    {
        Gitty\Loader::registerAutoload('ExampleNamespace\\Loader\\Class12');
    }
}
