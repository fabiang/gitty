<?php
namespace Gitty\Tests\Gitty\Remote;

use \Gitty as Gitty;

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/TestCase.php';

require_once \dirname(__FILE__).'/../../../library/Gitty/Repositories.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Config.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Config/Ini.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Remote.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Remote/AdapterAbstract.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Remote/Adapter/Ftp.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Remote/Exception.php';

class FtpAdapterTest extends \PHPUnit_Framework_TestCase
{
    protected $config = null;

    public function setUp()
    {
        $workingConfig = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../../data/workingExample.ini'
        );
        $remoteData = $workingConfig->projects->myproject->deployment->hostnamecom;

        $this->config = $remoteData;
    }

    public function tearDown()
    {
        $this->config = null;
    }

    /**
     * @covers Gitty\Remote\Adapter\Ftp::close
     */
    public function testClose()
    {
        $config = $this->config;
        $config->port = 21;
        $config->revisitionFileName = 'myRevFile.txt';
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        $remote->close();
        $remote->init();
        $remote->close();
    }

    /**
     * @covers Gitty\Remote\Adapter\Ftp::cleanUp
     */
    public function testCleanUp()
    {
        $remote = new Gitty\Remote\Adapter\Ftp($this->config);
        $remote->init();
        $remote->cleanUp();
    }

    /**
     * @covers Gitty\Remote\Adapter\Ftp::__destruct
     */
    public function testDestruct()
    {
        $remote = new Gitty\Remote\Adapter\Ftp($this->config);
        unset($remote);
    }

    /**
     * @expectedException Gitty\Remote\Exception
     * @covers Gitty\Remote\Adapter\Ftp::init
     * @covers Gitty\Remote\Exception
     */
    public function testInvalidHostName()
    {
        $config = clone $this->config;
        $config->hostname = \uniqid().'______.local';
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        try {
            $remote->init();
        } catch (Gitty\Remote\Exception $e) {
            $remote->close();
            throw $e;
        }
    }

    /**
     * @expectedException Gitty\Remote\Exception
     * @dataProvider provideInvalidLogins
     * @covers Gitty\Remote\Adapter\Ftp::init
     * @covers Gitty\Remote\Exception
     */
    public function testInvalidLogin($hostname, $port = 21, $username, $password, $path)
    {
        $config = new Gitty\Config(
            array(
                'hostname' => $hostname,
                'port'     => $port,
                'username' => $username,
                'password' => $password,
                'path'     => $path
            )
        );

        $remote = new Gitty\Remote\Adapter\Ftp($config);
        try {
            $remote->init();
        } catch (Gitty\Remote\Exception $e) {
            $remote->close();
            throw $e;
        }
    }

    /**
     * @covers Gitty\Remote\Adapter\Ftp::getServerRevisitionId
     * @covers Gitty\Remote\Adapter\Ftp::putServerRevisitionId
     */
    public function testGetServerRevisitionId()
    {
        $config = clone $this->config;
        $config->revisitionFileName = 'myRevFile.txt';
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        $remote->unlink($config->revisitionFileName);
        $remote->close();

        $this->assertEquals(null, $remote->getServerRevisitionId());
        $remote->close();

        $remote->putServerRevisitionId('test');
        $this->assertEquals('test', $remote->getServerRevisitionId());
        $remote->unlink($config->revisitionFileName);
        $remote->close();
    }

    /**
     * @covers Gitty\Remote\Adapter\Ftp::copy
     */
    public function testCopyUnknownFile()
    {
        $remote = new Gitty\Remote\Adapter\Ftp($this->config);
        $remote->init();
        $this->assertEquals(null, $remote->copy(\uniqid().'/unknown.txt', '/foo/bar'));
        $remote->close();
    }

    /**
     * @covers Gitty\Remote\Adapter\Ftp::getAdapterName
     */
    public function testGetAdapterName()
    {
        $remote = new Gitty\Remote\Adapter\Ftp($this->config);
        $this->assertEquals('Ftp', $remote->getAdapterName());
        $remote->close();
    }

    /**
     * @covers Gitty\Remote\Adapter\Ftp::__construct
     * @covers Gitty\Remote\Adapter\Ftp::getPort
     * @covers Gitty\Remote\AdapterAbstract::__get
     */
    public function testPortSetting()
    {
        $config = clone $this->config;
        $config->port = 21;
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        $this->assertEquals($config->port, $remote->getPort());
        $this->assertEquals($config->port, $remote->port);
        $remote->close();
    }

    /**
     * @covers Gitty\Remote\AdapterAbstract::__get
     * @covers Gitty\Remote\AdapterAbstract::getRevisitionFileName
     * @covers Gitty\Remote\Adapter\Ftp::__construct
     */
    public function testRevisitionFileNameSetting()
    {
        $config = clone $this->config;
        $config->revisitionFileName = \uniqid();
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        $this->assertEquals($config->revisitionFileName, $remote->getRevisitionFileName());
        $this->assertEquals($config->revisitionFileName, $remote->revisitionFileName);
        $remote->close();
    }

    /**
     * @covers Gitty\Remote\AdapterAbstract::__get
     * @covers Gitty\Remote\Adapter\Ftp::getHostname
     * @covers Gitty\Remote\Adapter\Ftp::getUsername
     * @covers Gitty\Remote\Adapter\Ftp::getPassword
     * @covers Gitty\Remote\Adapter\Ftp::getPath
     */
    public function testOtherConfigurationOptions()
    {
        $config = $this->config;
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        $config_array = $config->toArray();
        $this->assertEquals(
            $config_array,
            array(
                'adapter'  => strtolower($remote->getAdapterName()),
                'hostname' => $remote->getHostname(),
                'username' => $remote->getUsername(),
                'password' => $remote->getPassword(),
                'path'     => $remote->getPath()
            )
        );
        $this->assertEquals(
            $config_array,
            array(
                'adapter'  => strtolower($remote->getAdapterName()),
                'hostname' => $remote->hostname,
                'username' => $remote->username,
                'password' => $remote->password,
                'path'     => $remote->path
            )
        );
    }

    /**
     * @covers Gitty\Remote\AdapterAbstract::__get
     * @covers Gitty\Remote\AdapterAbstract::__set
     * @covers Gitty\Remote\AdapterAbstract::getRevisitionFileName
     * @covers Gitty\Remote\AdapterAbstract::setRevisitionFileName
     * @covers Gitty\Remote\AdapterAbstract::getOptions
     * @covers Gitty\Remote\AdapterAbstract::setOptions
     * @covers Gitty\Remote\AdapterAbstract::toArray
     * @covers Gitty\Remote\Adapter\Ftp::getHostname
     * @covers Gitty\Remote\Adapter\Ftp::getUsername
     * @covers Gitty\Remote\Adapter\Ftp::getPassword
     * @covers Gitty\Remote\Adapter\Ftp::getPath
     * @covers Gitty\Remote\Adapter\Ftp::getPort
     * @covers Gitty\Remote\Adapter\Ftp::setHostname
     * @covers Gitty\Remote\Adapter\Ftp::setUsername
     * @covers Gitty\Remote\Adapter\Ftp::setPassword
     * @covers Gitty\Remote\Adapter\Ftp::setPath
     * @covers Gitty\Remote\Adapter\Ftp::setPort
     */
    public function testConfigurationOptionsSetting()
    {
        $methods = array(
            'revisitionFileName',
            'hostname',
            'username',
            'password',
            'path'
        );

        $remote = new Gitty\Remote\Adapter\Ftp($this->config);

        foreach ($methods as $method) {
            $set = 'set'.\ucfirst($method);
            $get = 'get'.\ucfirst($method);
            $uid = \uniqid();
            $remote->$set($uid);
            $this->assertEquals($uid, $remote->$get());
        }

        foreach ($methods as $method) {
            $uid = \uniqid();
            $remote->$method = $uid;
            $this->assertEquals($uid, $remote->$method);
        }

        //port
        $uid = \uniqid();
        $remote->setPort($uid);
        $this->assertEquals($remote->getPort(), (int)$uid);

        $remote->port = $uid;
        $this->assertEquals($remote->port, (int)$uid);

        // test setting and getting all options
        $methods[] = 'port';
        $data = array();
        foreach ($methods as $method) {
            $data[$method] = $remote->$method;
        }
        $this->assertEquals($data, $remote->getOptions());

        $data['hostname'] = 'foobar.de';
        $data['username'] = 'foobar';
        $remote->setOptions($data);
        $this->assertEquals($data, $remote->getOptions());

        $this->assertEquals($data, $remote->toArray());

        // test if invalid options are skipped
        $data[\uniqid()] = \uniqid();
        $remote->setOptions($data);
        $this->assertNotEquals($data, $remote->getOptions());
    }

    /**
     * also tests auto init of connection
     *
     * @dataProvider provideFtpFunctions
     * @covers Gitty\Remote\Adapter\Ftp::init
     * @covers Gitty\Remote\Adapter\Ftp::rmDir
     * @covers Gitty\Remote\Adapter\Ftp::put
     * @covers Gitty\Remote\Adapter\Ftp::rename
     * @covers Gitty\Remote\Adapter\Ftp::unlink
     * @covers Gitty\Remote\Adapter\Ftp::copy
     */
    public function testFtpFunctions($function, $params)
    {
        $remote = new Gitty\Remote\Adapter\Ftp($this->config);
        \call_user_func_array(array($remote, $function), $params);
        $remote->cleanUp();
        $remote->close();
    }

    /**
     * @expectedException Gitty\Remote\Exception
     * @covers Gitty\Remote\AdapterAbstract::__set
     * @covers Gitty\Remote\Exception
     */
    public function testInvalidSet()
    {
        $remote = new Gitty\Remote\Adapter\Ftp($this->config);
        $uid = \uniqid();
        $remote->$uid = 1;
    }

    /**
     * @expectedException Gitty\Remote\Exception
     * @covers Gitty\Remote\AdapterAbstract::__get
     * @covers Gitty\Remote\Exception
     */
    public function testInvalidGet()
    {
        $remote = new Gitty\Remote\Adapter\Ftp($this->config);
        $uid = \uniqid();
        $foo = $remote->$uid;
    }

    /**
     * @covers Gitty\Remote\Adapter\Ftp::__toString
     */
    public function testToString()
    {
        $config = $this->config;
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        $test = "(FTP) {$config->hostname}:" . (isset($config->port) ? $config->port : 21);
        $this->assertEquals($test, (string)$remote);
    }

    /**
     * data provider
     */
    public static function provideInvalidLogins()
    {
        return array(
            array('ftp.mozilla.org', 21, 'username_'.uniqid(), 'password_'.uniqid(), '/'),
            array('ftp.sun.com',     21, 'username_'.uniqid(), 'password_'.uniqid(), '/'),
            array('ftp.kernel.org',  21, 'username_'.uniqid(), 'password_'.uniqid(), '/'),
        );
    }

    public function provideFtpFunctions()
    {
        $file_path = \realpath(\dirname(__FILE__).'/../../data/example/');
        return array(
            array('put',    array(\fopen($file_path.'/file1.txt', 'r'), 'file1.txt')),
            array('put',    array(\fopen($file_path.'/file2.txt', 'r'), 'file2.txt')),
            array('put',    array(\fopen($file_path.'/copy/file2.txt', 'r'), 'copy/file2.txt')),
            array('put',    array(\file_get_contents($file_path.'/renamed.txt'), 'foobar.txt')),
            array('rename', array('file1.txt', 'renamed.txt')),
            array('unlink', array('file2.txt')),
            array('copy',   array('renamed.txt', 'copied.txt')),
            array('copy',   array('copied.txt', 'copy/foobar/copied.txt')),
            array('unlink', array('copy/file2.txt')),
            array('unlink', array('copy/copied.txt')),
            array('unlink', array('copy/renamed.txt')),
            array('unlink', array('copy/foobar/copied.txt')),
        );
    }
}
