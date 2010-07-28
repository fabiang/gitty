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
    /**
     * @expectedException Gitty\Config\Exception
     * @dataProvider provideInexistantConfigFile
     * @covers Gitty\Config\Ini::__construct
     */
    public function testConfigFileExist($filename)
    {
        $conf = new Gitty\Config\Ini($filename);
    }

    /**
     * @expectedException Gitty\Config\Exception
     * @dataProvider provideBrokenConfigFiles
     * @covers Gitty\Config\Ini::__construct
     */
    public function testBrokenConfig($filename)
    {
        $conf = new Gitty\Config\Ini($filename);
    }

    /**
     * @expectedException Gitty\Config\Exception
     * @dataProvider provideExampleConfigs
     * @covers Gitty\Config\Ini::__construct
     */
    public function testConfigFileReadable($filename, $eConfig)
    {
        $chmod = \substr(\sprintf('%o', \fileperms($filename)), -4);
        \chmod($filename, 0000);
        try {
            $conf = new \Gitty\Config\Ini($filename);
        } catch(Gitty\Config\Exception $e) {
            \chmod($filename, \octdec($chmod));
            throw $e;
        }
        \chmod($filename, \octdec($chmod));
    }

    /**
     * @dataProvider provideExampleConfigs
     * @covers Gitty\Config\Ini::__construct
     */
    public function testConfigLoading($filename, $eConfig)
    {
        $conf = new Gitty\Config\Ini($filename);
        $this->assertEquals($eConfig, $conf->toArray());
    }

    /**
     * @dataProvider provideExampleConfigs
     * @covers Gitty\Config\Ini::toArray
     */
    public function testToArray($filename, $eConfig)
    {
        $conf = new Gitty\Config(array(), true, true);

        foreach($eConfig as $key => $value) {
            $conf->$key = $value;
        }

        $this->assertEquals($eConfig, $conf->toArray());
    }

    /**
     * @covers Gitty\Config::__get
     * @covers Gitty\Config::__set
     */
    public function testSetGet()
    {
        $conf = new Gitty\Config(array(), true, false);
        $conf->test = 1;
        $this->assertEquals($conf->toArray(), array('test' => 1));

        $conf = new Gitty\Config(array('test' => '1'));
        $this->assertEquals($conf->test, '1');

        $conf->test = array('bar', 'foo');
        $this->assertEquals(array('bar', 'foo'), $conf->test->toArray());
    }

    /**
     * @covers Gitty\Config::get
     */
    public function testGetDefault()
    {
        $conf = new Gitty\Config(array('test' => 1));

        $this->assertEquals(1, $conf->get('test', 2));
        $this->assertEquals(2, $conf->get('not_exists', 2));
    }

    /**
     * @covers Gitty\Config::__isset
     * @covers Gitty\Config::__unset
     */
    public function testIssetUnset()
    {
        $conf = new Gitty\Config(array('test' => 1));

        $this->assertTrue(isset($conf->test));
        unset($conf->test);
        $this->assertFalse(isset($conf->test));
    }

    /**
     * @covers Gitty\Config::getIterator
     */
    public function testIterator()
    {
        $should_be = array('test' => 1, 'bar' => 2);

        $conf = new Gitty\Config($should_be, true, false);

        $this->assertTrue(
            $conf->getIterator() instanceof \IteratorAggregate or
            $conf->getIterator() instanceof \Iterator
        );

        $test_it = array();
        foreach($conf as $key => $value) {
            $test_it[$key] = $value;
        }

        $this->assertEquals($test_it, $should_be);
    }

    /**
     * @covers Gitty\Config::offsetSet
     * @covers Gitty\Config::offsetExists
     * @covers Gitty\Config::offsetUnset
     * @covers Gitty\Config::offsetGet
     */
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

    /**
     * @dataProvider provideExampleConfigs
     * @covers Gitty\Config::__construct
     */
    public function testIniClass($filename, $eConfig)
    {
        $conf1 = new Gitty\Config($eConfig);
        $conf2 = new Gitty\Config\Ini($filename);
        $this->assertEquals($conf1->toArray(), $conf2->toArray());
    }

    /**
     * @dataProvider provideExampleConfigs
     * @covers Gitty\Config::__construct
     */
    public function testConfigObjectLoading($filename, $eConfig)
    {
        $conf1 = new Gitty\Config\Ini($filename);
        $conf2 = new Gitty\Config($conf1);
    }

    /**
     * @expectedException Gitty\Config\Exception
     * @covers Gitty\Config::__construct
     */
    public function testConfigObjectLoadingInvalid()
    {
        self::load('library/Gitty/Version.php');
        $conf1 = new Gitty\Version($filename);
        $conf2 = new Gitty\Config($conf1);
    }

    /**
     * @expectedException Gitty\Config\Exception
     * @dataProvider provideNoArrayOrObject
     * @covers Gitty\Config::__construct
     */
    public function testConfigNoArrayOrObject($data)
    {
        $conf = new Gitty\Config($data);
    }

    /**
     * @expectedException Gitty\Config\Exception
     * @covers Gitty\Config::__construct
     * @covers Gitty\Config::__unset
     */
    public function testConfigNotModificationableUnset()
    {
        $conf = new Gitty\Config(array('test' => 1), false);
        unset($conf->test);
    }

    /**
     * @expectedException Gitty\Config\Exception
     * @covers Gitty\Config::__construct
     * @covers Gitty\Config::__set
     */
    public function testConfigNotModificationableSet()
    {
        $conf = new Gitty\Config(array(), false);
        $conf->test = 1;
    }

    /**
     * @covers Gitty\Config::_mergeConfiguration
     * @covers Gitty\Config::__construct
     */
    public function testConfigMerging()
    {
        $default = Gitty\Config::$defaultConfig;

        Gitty\Config::$defaultConfig = array(
            'bar' => 'foo',
            'other' => array(
                'one' => 1,
                'two' => 2
            )
        );
        $conf = new Gitty\Config(
            array(
                'test' => 1,
                'other' => array(
                    'one' => 1,
                    'two' => 2
                )
            ),
            false,
            true
        );

        $this->assertEquals(
            array(
                'other' => array(
                    'one' => 1,
                    'two' => 2
                ),
                'test' => 1,
                'bar'  => 'foo'
            ),
            $conf->toArray()
        );

        // default config isn't an array
        Gitty\Config::$defaultConfig = 1;
        $conf = new Gitty\Config(
            array(
                'foobar' => 1,
                22232 => 'foobar',
                'other' => array(
                    'one' => 1,
                    'two' => 2
                )
            ),
            false,
            true
        );

        $this->assertEquals(
            array(
                0 => 1,
                'foobar' => 1,
                22232  => 'foobar',
                'other' => array(
                    'one' => 1,
                    'two' => 2
                )
            ),
            $conf->toArray()
        );

        Gitty\Config::$defaultConfig = $default;
    }

    /**
     * helper functions
     */
    public static function load($file)
    {
        include_once(\dirname(__FILE__).'/../../'.$file);
    }

    /**
     * data providers
     */
    public static function provideInexistantConfigFile()
    {
        return array(
            array(\dirname(__FILE__).'/../data/'.\uniqid('gitty_config___')),
            array(\dirname(__FILE__).'/../data/'.\uniqid('gitty_config___')),
            array(\dirname(__FILE__).'/../data/'.\uniqid('gitty_config___')),
            array(\dirname(__FILE__).'/../data/'.\uniqid('gitty_config___'))
        );
    }

    /**
     * test for broken3.ini
     */
    public static function provideBrokenConfigFiles()
    {
        return array(
            array(\dirname(__FILE__).'/../data/broken.ini'),
            array(\dirname(__FILE__).'/../data/broken2.ini'),
            //array(\dirname(__FILE__).'/../data/broken3.ini')
        );
    }

    public static function provideExampleConfigs()
    {
        return array(
            array(
                \dirname(__FILE__).'/../data/example.ini',
                array(
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
                            'path' => '/path/to/gitty/tests/data/example',
                            'description' => '',
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
                )
            )
        );
    }

    public static function provideNoArrayOrObject()
    {
        return array(
            array(1),
            array('string'),
            array(true),
            array(false)
        );
    }
}
