<?php
namespace Gitty\Tests\Gitty;

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/TestCase.php';

require_once \dirname(__FILE__).'/../../library/Gitty/Config.php';

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
                    'adapter' => array('ftp'),
                    'hostname' => array('hostname.com'),
                    'username' => array('test'),
                    'password' => array('test'),
                    'path' => array('/'),
                )
            )
        )
    );

    /**
     * @expectedException Gitty\Exception
     */
    public function testConfigFileExist()
    {
        $filename = \dirname(__FILE__) . '/../data/' . \uniqid("gittyconfig_");
        $conf = new \Gitty\Config($filename);
    }

    /**
     * @expectedException Gitty\Exception
     */
    public function testConfigFileReadable()
    {
        $filename = \dirname(__FILE__).'/../data/example.ini';
        $chmod = \substr(\sprintf('%o', \fileperms($filename)), -4);
        \chmod($filename, 0000);
        try {
            $conf = new \Gitty\Config($filename);
        } catch(\Gitty\Exception $e) {
            \chmod($filename, \octdec($chmod));
            throw $e;
        }
    }

    /**
     * @expectedException Gitty\Exception
     */
    public function testBrokenConfig()
    {
        $filename = \dirname(__FILE__) . '/../data/broken.ini';
        $conf = new \Gitty\Config($filename);
    }

    public function testConfigLoading()
    {
        $filename = \dirname(__FILE__).'/../data/example.ini';
        $conf = new \Gitty\Config($filename);

        $this->assertEquals($conf->toArray(), $this->exampleConfig);
    }

    public function testToArray()
    {
        $filename = \dirname(__FILE__).'/../data/empty.ini';
        $conf = new \Gitty\Config($filename);

        foreach($this->exampleConfig as $key => $value) {
            $conf->$key = $value;
        }

        $this->assertEquals($conf->toArray(), $this->exampleConfig);
    }
}