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
     *@covers Gitty\Loader::loadClass
     */
    public function testSearchDirsTypes()
    {
        $dir = \dirname(__FILE__).'/../../library';
        Gitty\Loader::loadClass('Gitty\\Version', $dir);
        Gitty\Loader::loadClass('Gitty\\Deployment', array($dir));
    }

    /**
     * @expectedException Gitty\Exception
     * @covers Gitty\Loader::loadClass
     */
    public function testSearchDirsInexistantString()
    {
        Gitty\Loader::loadClass('Gitty\\Remote\\Exception', 'not_existant');
    }

    /**
     * @expectedException Gitty\Exception
     * @covers Gitty\Loader::loadClass
     */
    public function testSearchDirsInexistantArray()
    {
        Gitty\Loader::loadClass('Gitty\\Repositories\\Exception', array('not_existant'));
    }

    /**
     * @covers Gitty\Loader::loadClass
     */
    public function testIncludePath()
    {
        $inc_path = \get_include_path();
        \set_include_path(\dirname(__FILE__).'/../../library');
        Gitty\Loader::loadClass('Gitty\\Observer\\Exception');
        \set_include_path($inc_path);
    }

    /**
     * @covers Gitty\Loader::loadFile
     */
    public function testLoadFile()
    {
        $dir = \dirname(__FILE__).'/../../library/Gitty/Repositories';
        Gitty\Loader::loadFile('AdapterAbstract.php', $dir, true);

        Gitty\Loader::loadFile('Git.php', array($dir.'/Adapter'), true);

        $inc_path = \get_include_path();
        \set_include_path(\dirname(__FILE__).'/../../library/Gitty/Remote');
        Gitty\Loader::loadFile('AdapterAbstract.php', null, true);
        \set_include_path($inc_path);
    }

    /**
     * @expectedException Gitty\Exception
     * @covers Gitty\Loader::securityCheck
     */
    public function testSecurityCheck()
    {
        $inc_path = \get_include_path();
        \set_include_path(\dirname(__FILE__).'/../../library');
        Gitty\Loader::loadClass('$%invalid.-filename/()');
        \set_include_path($inc_path);
    }

    /**
     * @covers Gitty\Loader::registerAutoload
     * @covers Gitty\Loader::autoload
     */
    public function testAutoLoad()
    {
        if (!\function_exists('spl_autoload_register')) {
            $this->setExpectedException('\Gitty\Exception');
            Gitty\Loader::registerAutoload();
        } else {
            $inc_path = \get_include_path();
            \set_include_path(\dirname(__FILE__).'/../../library');
            Gitty\Loader::registerAutoload();

            $loaded = new Gitty\Observer\Html();

            Gitty\Loader::registerAutoload(null, false);
            \set_include_path($inc_path);

            $this->assertEquals('\Gitty\Observer\Html', Gitty\Loader::autoLoad('\Gitty\Observer\Html'));
            $this->assertFalse(Gitty\Loader::autoLoad('\Gitty\fsdfsfsdf'));
        }
    }

    /**
     * @covers Gitty\Loader::__construct
     */
    public function testAutoloadConstruction()
    {
        if (!\function_exists('spl_autoload_register')) {
            $this->setExpectedException('\Gitty\Exception');
            Gitty\Loader::registerAutoload();
        } else {
            $inc_path = \get_include_path();
            \set_include_path(\dirname(__FILE__).'/../../library');

            $loader = new Gitty\Loader();
            $loaded = new Gitty\Remote\Adapter\Ftp(new Gitty\Config(array()));

            Gitty\Loader::registerAutoload(null, false);
            \set_include_path($inc_path);
        }
    }

    /**
     * @covers Gitty\Loader::loadClass
     */
    public function testSlashPrefix()
    {
        Gitty\Loader::loadClass('\\Gitty\\Remote\\Exception', \dirname(__FILE__).'/../../library');
    }

    /**
     * @covers Gitty\Loader::loadClass
     */
    public function testDotDir()
    {
        Gitty\Loader::loadClass('Gitty\\Repositories\\Exception', array('.', \dirname(__FILE__).'/../../library'));
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
        Gitty\Loader::registerAutoload('Gitty\Version');
    }

    /**
     * not finished yet
     * @covers Gitty\Loader::registerAutoload
     */
    public function testMissingSplAutoload()
    {
        $this->markTestIncomplete(
          'test incomplete'
        );

        /*if (\class_exists('\\Runkit_Sandbox')) {
            $sandbox = new \Runkit_Sandbox(array('disable_functions' => 'spl_autoload_register'));
            $sandbox->eval('Gitty\Loader::registerAutoload();');
        }*/
    }
}
