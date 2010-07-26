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
    protected $connectionRemote = null;

    protected function getConnectionRemote()
    {
        if (null === $this->connectionRemote) {
            // set configuration file with full functional ftp
            $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../config-homebox.ini');
            // set javascriptq and lubyte to your own
            $remote = new Gitty\Remote($config->projects->javascriptq->deployment->lubyte);
            $this->connectionRemote = $remote;
        }

        return $this->connectionRemote;
    }

    public function testSetDefaultAdapter()
    {
        $adapter = 'Gitty\\Remote\\Adapter\\Ftp';
        $this->assertEquals(Gitty\Remote::getDefaultAdapter(), $adapter);

        Gitty\Remote::setDefaultAdapter($adapter);

        $this->assertEquals(Gitty\Remote::getDefaultAdapter(), $adapter);
    }

    /**
     * @expectedException Gitty\Remotes\Exception
     */
    public function testSetDefaultUnknownAdapter()
    {
        $this->markTestIncomplete(
          'test incomplete'
        );

        /*
        $adapter = 'Gitty\\Version';
        Gitty\Repositories::setDefaultAdapter($adapter);
        */
    }

    public function testDefaultAdapterLoading()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../data/remoteKnownAdapter.ini');
        $remote = new Gitty\Remote($config->projects->myproject->deployment->hostnamecom);
    }

    public function testInit()
    {
        $this->getConnectionRemote()->init();
    }

    public function testRevisionId()
    {
        $uid = \uniqid();
        $this->getConnectionRemote()->putServerRevisitionId($uid);
        $this->assertEquals($this->getConnectionRemote()->getServerRevisitionId(), $uid);
    }

    public function testPut()
    {
        $this->getConnectionRemote()->put('Some Data', '/tests/test.txt');
        $this->getConnectionRemote()->put(\fopen(\dirname(__FILE__).'/../data/include.php', 'r'), '/tests/test.txt');
    }

    public function testCopy()
    {
        $this->getConnectionRemote()->copy('/tests/test.txt', '/test2/test.txt');
    }

    public function testRename()
    {
        $this->getConnectionRemote()->rename('/test2/test.txt', '/test2/test1.txt');
        $this->getConnectionRemote()->rename('/test2/test1.txt', '/foo/test.txt');
    }

    public function testUnlink()
    {
        $this->getConnectionRemote()->unlink('/foo/test.txt');
        $this->getConnectionRemote()->unlink('/tests/test.txt');
    }

    public function testGetAdapter()
    {
        $adapter = $this->getConnectionRemote()->getAdapter();
        $this->assertTrue($adapter instanceof Gitty\Remote\AdapterAbstract);
        $this->assertEquals(get_class($adapter), 'Gitty\\Remote\\Adapter\\Ftp');
    }

    public function testToString()
    {
        $adapter = $this->getConnectionRemote()->getAdapter();
        $this->assertEquals((string)$adapter, (string)$this->getConnectionRemote());
    }

    public function testCleanUp()
    {
        $this->getConnectionRemote()->cleanUp();
    }
}
