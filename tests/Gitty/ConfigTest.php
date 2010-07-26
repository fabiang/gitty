<?php
namespace Gitty\Tests\Gitty;

use \Gitty as Gitty;

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/TestCase.php';

require_once \dirname(__FILE__).'/../../library/Gitty/Config/Exception.php';
require_once \dirname(__FILE__).'/../../library/Gitty/Config.php';
require_once \dirname(__FILE__).'/../../library/Gitty/Config/Ini.php';

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected $exampleConfig = array(
        'global' => array(
            'git' => array(
                'defaultGitDir' => '.git',
                'descriptionFile' => 'description',
                'binLocation'=> '/usr/bin/git'
            ),
            'gitty' => array(
                'readDirectories' => '0',
                'dateFormat' => 'Y-m-d H:i:s',
                'revistionFile' => 'revision.txt',
                'tempDir' => '/tmp'
            )
        ),
        'projects' => array(
            'myproject' => array(
                'name' => 'Myproject',
                'repository' => '/home/git/repositories/myproject',
                'description' => 'overwrite description',
                'showBranches' => 1,
                'deployment' => array(
                    'hostnamecom' => array(
                        'adapter' => 'ftp',
                        'hostname' => 'hostname.com',
                        'username' => 'test',
                        'password' => 'test',
                        'path' => '/'
                    )
                )
            )
        )
    );

    /**
     * @expectedException Gitty\Config\Exception
     */
    public function testConfigFileExist()
    {
        $filename = \dirname(__FILE__) . '/../data/' . \uniqid("gittyconfig_");
        $conf = new Gitty\Config\Ini($filename);
    }

    /**
     * @expectedException Gitty\Config\Exception
     */
    public function testConfigFileReadable()
    {
        $filename = \dirname(__FILE__).'/../data/example.ini';
        $chmod = \substr(\sprintf('%o', \fileperms($filename)), -4);
        \chmod($filename, 0000);
        try {
            $conf = new \Gitty\Config\Ini($filename);
        } catch(Gitty\Config\Exception $e) {
            \chmod($filename, \octdec($chmod));
            throw $e;
        }
    }

    /**
     * @expectedException Gitty\Config\Exception
     */
    public function testBrokenConfig()
    {
        $filename = \dirname(__FILE__) . '/../data/broken.ini';
        $conf = new Gitty\Config\Ini($filename);
    }

    public function testConfigLoading()
    {
        $filename = \dirname(__FILE__).'/../data/example.ini';
        $conf = new Gitty\Config\Ini($filename);

        $this->assertEquals($conf->toArray(), $this->exampleConfig);
    }

    public function testToArray()
    {
        $conf = new Gitty\Config(array(), true, true);

        foreach($this->exampleConfig as $key => $value) {
            $conf->$key = $value;
        }

        $this->assertEquals($conf->toArray(), $this->exampleConfig);
    }

    public function testSetGet()
    {
        $conf = new Gitty\Config(array(), true, false);
        $conf->test = 1;
        $this->assertEquals($conf->toArray(), array('test' => 1));

        $conf = new Gitty\Config(array('test' => '1'));
        $this->assertEquals($conf->test, '1');
    }

    public function testGetDefault()
    {
        $conf = new Gitty\Config(array('test' => 1));

        $this->assertEquals($conf->get('test', 2), 1);
        $this->assertEquals($conf->get('not_exists', 2), 2);
    }

    public function testIssetUnset()
    {
        $conf = new Gitty\Config(array('test' => 1));

        $this->assertTrue(isset($conf->test));
        unset($conf->test);
        $this->assertFalse(isset($conf->test));
    }

    public function testIterator()
    {
        $should_be = array('test' => 1, 'bar' => 2);

        $conf = new Gitty\Config($should_be, true, false);

        $this->assertTrue($conf->getIterator() instanceof \IteratorAggregate);

        $test_it = array();
        foreach($conf as $key => $value) {
            $test_it[$key] = $value;
        }

        $this->assertEquals($test_it, $should_be);
    }

    public function testArrayAccess()
    {
        $conf = new Gitty\Config(array());

        $conf['test'] = 1;
        $this->assertTrue(isset($conf['test']));
        $this->assertEquals($conf['test'], 1);
        unset($conf['test']);
        $this->assertFalse(isset($conf['test']));

        $conf->second = 2;
        $this->assertTrue(isset($conf['second']));
        $this->assertEquals($conf->second, 2);
    }

    public function testIniClass()
    {
        $conf1 = new Gitty\Config($this->exampleConfig);

        $filename = \dirname(__FILE__).'/../data/example.ini';
        $conf2 = new Gitty\Config\Ini($filename);

        $this->assertEquals($conf1->toArray(), $conf2->toArray());
    }

    public function testConfigObjectLoading()
    {
        $filename = \dirname(__FILE__).'/../data/example.ini';
        $conf1 = new Gitty\Config\Ini($filename);

        $conf2 = new Gitty\Config($conf1);
    }

    /**
     * @expectedException Gitty\Config\Exception
     */
    public function testConfigObjectLoadingInvalid()
    {
        require_once \dirname(__FILE__).'/../../library/Gitty/Version.php';
        $conf1 = new Gitty\Version($filename);
        $conf2 = new Gitty\Config($conf1);
    }

    /**
     * @expectedException Gitty\Config\Exception
     */
    public function testConfigNoArrayOrObject()
    {
        $conf = new Gitty\Config(1);
    }

    /**
     * @expectedException Gitty\Config\Exception
     */
    public function testConfigNotModificationableUnset()
    {
        $conf = new Gitty\Config(array('test' => 1), false);
        unset($conf->test);
    }

    /**
     * @expectedException Gitty\Config\Exception
     */
    public function testConfigNotModificationableSet()
    {
        $conf = new Gitty\Config(array(), false);
        $conf->test = 1;
    }

    public function testConfigMerging()
    {
        Gitty\Config::$defaultConfig = 1;
        $conf = new Gitty\Config(array(), false);
    }

    /**
     * @expectedException Gitty\Config\Exception
     */
    public function testInvalidIniKey()
    {
        $filename = \dirname(__FILE__).'/../data/broken2.ini';
        $conf = new Gitty\Config\Ini($filename);
    }

    public function testInvaliddublicateKey()
    {
        $filename = \dirname(__FILE__).'/../data/broken3.ini';
        $conf = new Gitty\Config\Ini($filename);
    }
}
