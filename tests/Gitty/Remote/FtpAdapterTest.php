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
    /**
     * @dataProvider provideWorkingConfig
     * @covers Gitty\Remote\Adapter\Ftp::close
     */
    public function testClose($config)
    {
        $config->port = 21;
        $config->revisitionFileName = 'myRevFile.txt';
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        $remote->close();
    }

    /**
     * @expectedException Gitty\Remote\Exception
     * @dataProvider provideWorkingConfig
     * @covers Gitty\Remote\Adapter\Ftp::init
     * @covers Gitty\Remote\Exception
     */
    public function testInvalidHostName($config)
    {
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
     * @dataProvider provideWorkingConfig
     * @covers Gitty\Remote\Adapter\Ftp::getServerRevisitionId
     */
    public function testGetServerRevisitionId($config)
    {
        $config->revisitionFileName = 'myRevFile.txt';
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        $this->assertEquals($remote->getServerRevisitionId(), null);
        $remote->close();
    }

    /**
     * @dataProvider provideWorkingConfig
     * @covers Gitty\Remote\Adapter\Ftp::copy
     */
    public function testCopyUnknownFile($config)
    {
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        $remote->init();
        $this->assertEquals($remote->copy(\uniqid().'/unknown.txt', '/foo/bar'), null);
        $remote->close();
    }

    /**
     * @dataProvider provideWorkingConfig
     * @covers Gitty\Remote\Adapter\Ftp::getAdapterName
     */
    public function testGetAdapterName($config)
    {
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        $this->assertEquals($remote->getAdapterName(), 'Ftp');
        $remote->close();
    }

    /**
     * @dataProvider provideWorkingConfig
     * @covers Gitty\Remote\Adapter\Ftp::__construct
     * @covers Gitty\Remote\Adapter\Ftp::getPort
     * @covers Gitty\Remote\AdapterAbstract::__get
     */
    public function testPortSetting($config)
    {
        $config->port = 21;
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        $this->assertEquals($config->port, $remote->getPort());
        $this->assertEquals($config->port, $remote->port);
        $remote->close();
    }

    /**
     * @dataProvider provideWorkingConfig
     * @covers Gitty\Remote\AdapterAbstract::__get
     * @covers Gitty\Remote\AdapterAbstract::getRevisitionFileName
     * @covers Gitty\Remote\Adapter\Ftp::__construct
     */
    public function testRevisitionFileNameSetting($config)
    {
        $config->revisitionFileName = \uniqid();
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        $this->assertEquals($config->revisitionFileName, $remote->getRevisitionFileName());
        $this->assertEquals($config->revisitionFileName, $remote->revisitionFileName);
        $remote->close();
    }

    /**
     * @dataProvider provideWorkingConfig
     * @covers Gitty\Remote\AdapterAbstract::__get
     * @covers Gitty\Remote\Adapter\Ftp::getHostname
     * @covers Gitty\Remote\Adapter\Ftp::getUsername
     * @covers Gitty\Remote\Adapter\Ftp::getPassword
     * @covers Gitty\Remote\Adapter\Ftp::getPath
     */
    public function testOtherConfigurationOptions($config)
    {
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
     * @dataProvider provideWorkingConfig
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
    public function testConfigurationOptionsSetting($config)
    {
        $methods = array(
            'revisitionFileName',
            'hostname',
            'username',
            'password',
            'path'
        );

        $remote = new Gitty\Remote\Adapter\Ftp($config);

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
     * @covers Gitty\Remote\Adapter\Ftp::rmDir
     * @covers Gitty\Remote\Adapter\Ftp::put
     * @covers Gitty\Remote\Adapter\Ftp::rename
     * @covers Gitty\Remote\Adapter\Ftp::unlink
     * @covers Gitty\Remote\Adapter\Ftp::copy
     */
    public function testFtpFunctions($config, $function, $params)
    {
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        \call_user_func_array(array($remote, $function), $params);
        $remote->cleanUp();
        $remote->close();
    }

    /**
     * @expectedException Gitty\Remote\Exception
     * @dataProvider provideWorkingConfig
     * @covers Gitty\Remote\AdapterAbstract::__set
     * @covers Gitty\Remote\Exception
     */
    public function testInvalidSet($config)
    {
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        $uid = \uniqid();
        $remote->$uid = 1;
    }

    /**
     * @expectedException Gitty\Remote\Exception
     * @dataProvider provideWorkingConfig
     * @covers Gitty\Remote\AdapterAbstract::__get
     * @covers Gitty\Remote\Exception
     */
    public function testInvalidGet($config)
    {
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        $uid = \uniqid();
        $foo = $remote->$uid;
    }

    /**
     * @dataProvider provideWorkingConfig
     * @covers Gitty\Remote\Adapter\Ftp::__toString
     */
    public function testToString($config)
    {
        $remote = new Gitty\Remote\Adapter\Ftp($config);
        $test = "(FTP) {$config->hostname}:" . (isset($config->port) ? $config->port : 21);
        $this->assertEquals((string)$remote, $test);
    }

    /**
     * data provider
     */
    public static function provideWorkingConfig()
    {
        $workingConfig = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../../data/workingExample.ini'
        );
        $remoteData = $workingConfig->projects->myproject->deployment->hostnamecom;

        return array(
            array($remoteData)
        );
    }

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
        $config = self::provideWorkingConfig();
        $config = $config[0][0];
        $file_path = \realpath(\dirname(__FILE__).'/../../data/example/');
        return array(
            array($config, 'put',    array(\fopen($file_path.'/file1.txt', 'r'), 'file1.txt')),
            array($config, 'put',    array(\fopen($file_path.'/file2.txt', 'r'), 'file2.txt')),
            array($config, 'put',    array(\fopen($file_path.'/copy/file2.txt', 'r'), 'copy/file2.txt')),
            array($config, 'put',    array(\file_get_contents($file_path.'/renamed.txt'), 'foobar.txt')),
            array($config, 'rename', array('file1.txt', 'renamed.txt')),
            array($config, 'unlink', array('file2.txt')),
            array($config, 'copy',   array('renamed.txt', 'copied.txt')),
            array($config, 'copy',   array('copied.txt', 'copy/foobar/copied.txt')),
            array($config, 'unlink', array('copy/file2.txt')),
            array($config, 'unlink', array('copy/copied.txt')),
            array($config, 'unlink', array('copy/renamed.txt')),
            array($config, 'unlink', array('copy/foobar/copied.txt')),
        );
    }
}
