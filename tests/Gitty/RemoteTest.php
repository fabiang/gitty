<?php
namespace Gitty\Tests\Gitty;

use \Gitty as Gitty;

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/TestCase.php';

require_once \dirname(__FILE__).'/../../library/Gitty/Repositories.php';
require_once \dirname(__FILE__).'/../../library/Gitty/Config.php';
require_once \dirname(__FILE__).'/../../library/Gitty/Config/Ini.php';
require_once \dirname(__FILE__).'/../../library/Gitty/Remote.php';
require_once \dirname(__FILE__).'/../../library/Gitty/Remote/AdapterAbstract.php';
require_once \dirname(__FILE__).'/../../library/Gitty/Remote/Adapter/Ftp.php';

class RemoteTest extends \PHPUnit_Framework_TestCase
{
    protected $defaultAdapter = null;

    public function setUp()
    {
        $this->defaultAdapter = Gitty\Remote::getDefaultAdapter();
    }

    public function tearDown()
    {
        Gitty\Remote::setDefaultAdapter($this->defaultAdapter);
    }

    /**
     * @covers Gitty\Remote::setDefaultAdapter
     * @covers Gitty\Remote::getDefaultAdapter
     */
    public function testSetDefaultAdapter()
    {

        $adapter = 'Gitty\\Remote\\Adapter\\Foobar';
        Gitty\Remote::setDefaultAdapter($adapter);
        $this->assertEquals($adapter, Gitty\Remote::getDefaultAdapter());
    }

    /**
     * @expectedException Gitty\Remote\Exception
     * @covers Gitty\Remote::__construct
     * @covers Gitty\Exception
     */
    public function testDefaultAdapterClassNotFound()
    {
        Gitty\Remote::setDefaultAdapter('Gitty\\Versiondfdsfdf');

        $config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/remoteEmptyAdapter.ini'
        );

        $remote = new Gitty\Remote(
            $config->projects->myproject->deployment->hostnamecom
        );
    }

    /**
     * @expectedException Gitty\Remote\Exception
     * @covers Gitty\Remote::__construct
     * @covers Gitty\Exception
     */
    public function testDefaultAdapterClassInvalid()
    {
        Gitty\Remote::setDefaultAdapter('Gitty\\Version');

        $config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/remoteEmptyAdapter.ini'
        );

        $remote = new Gitty\Remote(
            $config->projects->myproject->deployment->hostnamecom
        );
    }

    /**
     * @expectedException Gitty\Remote\Exception
     * @covers Gitty\Remote::__construct
     * @covers Gitty\Exception
     */
    public function testUnkownAdapterInConfig()
    {
        $config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/remoteUnknownAdapter.ini'
        );

        $remote = new Gitty\Remote(
            $config->projects->myproject->deployment->hostnamecom
        );
    }

    /**
     * @covers Gitty\Remote::__construct
     */
    public function testDefaultAdapterLoading()
    {
        $config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/remoteEmptyAdapter.ini'
        );
        $remote = new Gitty\Remote(
            $config->projects->myproject->deployment->hostnamecom
        );
        $adapter_used = $remote->getAdapter();
        $default_adapter = Gitty\Remote::getDefaultAdapter();
        $this->assertEquals(
            \get_class($adapter_used),
            $default_adapter
        );
    }

    /**
     * @dataProvider provideWorkingRemote
     * @covers Gitty\Remote::init
     */
    public function testInit($remote)
    {
        $remote->init();
    }

    /**
     * @dataProvider provideWorkingRemote
     * @covers Gitty\Remote::putServerRevisitionId
     * @covers Gitty\Remote::getServerRevisitionId
     */
    public function testRevisionId($remote)
    {
        $uid = \uniqid();
        $remote->putServerRevisitionId($uid);
        $this->assertEquals($remote->getServerRevisitionId(), $uid);
    }

    /**
     * @dataProvider provideWorkingRemote
     * @covers Gitty\Remote::put
     */
    public function testPut($remote)
    {
        $remote->put('Some Data', '/tests/test.txt');
        $remote->put(\fopen(\dirname(__FILE__).'/../data/include.php', 'r'), '/tests/test.txt');
    }

    /**
     * @dataProvider provideWorkingRemote
     * @covers Gitty\Remote::copy
     */
    public function testCopy($remote)
    {
        $remote->copy('/tests/test.txt', '/test2/test.txt');
    }

    /**
     * @dataProvider provideWorkingRemote
     * @covers Gitty\Remote::rename
     */
    public function testRename($remote)
    {
        $remote->rename('/test2/test.txt', '/test2/test1.txt');
        $remote->rename('/test2/test1.txt', '/foo/test.txt');
    }

    /**
     * @dataProvider provideWorkingRemote
     * @covers Gitty\Remote::unlink
     */
    public function testUnlink($remote)
    {
        $remote->unlink('/foo/test.txt');
        $remote->unlink('/tests/test.txt');
    }

    /**
     * @dataProvider provideWorkingRemote
     * @covers Gitty\Remote::getAdapter
     */
    public function testGetAdapter($remote)
    {
        $adapter = $remote->getAdapter();
        $this->assertTrue($adapter instanceof Gitty\Remote\AdapterAbstract);
        $this->assertEquals(\get_class($adapter), 'Gitty\\Remote\\Adapter\\Ftp');
    }

    /**
     * @dataProvider provideWorkingRemote
     * @covers Gitty\Remote::getAdapter
     * @covers Gitty\Remote::__toString
     */
    public function testToString($remote)
    {
        $adapter = $remote->getAdapter();
        $this->assertEquals((string)$adapter, (string)$remote);
    }

    /**
     * @dataProvider provideWorkingRemote
     * @covers Gitty\Remote::cleanUp
     */
    public function testCleanUp($remote)
    {
        $remote->cleanUp();
    }

    /**
     * data providers
     */
    public static function provideWorkingRemote()
    {
        $workingConfig = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/workingExample.ini'
        );
        $remoteData = $workingConfig->projects->myproject->deployment->hostnamecom;

        $remote = new Gitty\Remote($remoteData);

        return array(
            array($remote)
        );
    }
}
