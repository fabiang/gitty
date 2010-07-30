<?php
namespace Gitty\Tests\Gitty\Repositories;

use \Gitty as Gitty;

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/TestCase.php';

require_once \dirname(__FILE__).'/../../../library/Gitty/Repositories.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Config.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Config/Ini.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Remote.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Repositories/AdapterAbstract.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Repositories/Adapter/Git.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Remote/AdapterAbstract.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Remote/Adapter/Ftp.php';

class GitAdapterTest extends \PHPUnit_Framework_TestCase
{
    protected $config = null;

    public function setUp()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');
        $config = $config->projects->myproject;
        $config->path = \realpath(\dirname(__FILE__).'/../../data/example');
        $this->config = $config;
    }

    public function teatDown()
    {
        $this->config = null;
    }

    /**
     * @covers Gitty\Repositories\AdapterAbstract::__set
     * @covers Gitty\Repositories\AdapterAbstract::__get
     * @covers Gitty\Repositories\AdapterAbstract::getName
     * @covers Gitty\Repositories\AdapterAbstract::setName
     * @covers Gitty\Repositories\AdapterAbstract::getDescription
     * @covers Gitty\Repositories\AdapterAbstract::setDescription
     * @covers Gitty\Repositories\AdapterAbstract::getPath
     * @covers Gitty\Repositories\AdapterAbstract::setPath
     * @covers Gitty\Repositories\Adapter\Git::getOwner
     * @covers Gitty\Repositories\Adapter\Git::setOwner
     */
    public function testAdapterOptions()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);

        $options = array('name', 'description', 'path', 'owner');
        foreach ($options as $option) {
            $function_set = 'set'.ucfirst($option);
            $function_get = 'get'.ucfirst($option);
            $repo->$function_set('test');
            $this->assertEquals($repo->$function_get(), 'test');
        }

        foreach ($options as $option) {
            $repo->$option = 'test';
            $this->assertEquals($repo->$option, 'test');
        }
    }

    /**
     * @expectedException Gitty\Repositories\Exception
     * @covers Gitty\Repositories\AdapterAbstract::__set
     */
    public function testSetUnkownOption()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);

        $u = \uniqid();
        $repo->$u = 'test';
    }

    /**
     * @expectedException Gitty\Repositories\Exception
     * @covers Gitty\Repositories\AdapterAbstract::__get
     */
    public function testGetUnkownOption()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);

        $u = \uniqid();
        $foo = $repo->$u;
    }

    /**
     * @covers Gitty\Repositories\AdapterAbstract::toArray
     * @covers Gitty\Repositories\AdapterAbstract::getOptions
     * @covers Gitty\Repositories\AdapterAbstract::setOptions
     */
    public function testGetSetOptions()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);

        $options = $repo->getOptions();
        $this->assertEquals($options, $repo->toArray());

        foreach ($options as $option_name => $option) {
            $func = 'get'. \ucfirst($option_name);
            $this->assertEquals($option, $repo->$func());
        }

        $repo->setOptions($options);
        $this->assertEquals($repo->getOptions(), $options);
    }

    /**
     * @covers Gitty\Repositories\AdapterAbstract::getPath
     * @covers Gitty\Repositories\AdapterAbstract::setPath
     */
    public function testSetPath()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);

        $repo->setPath('/foo/bar/');
        $this->assertEquals($repo->getPath(), '/foo/bar');
    }

    /**
     * @covers Gitty\Repositories\AdapterAbstract::getAdapterName
     */
    public function testAdapterName()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);
        $this->assertEquals($repo->getAdapterName(), 'Git');
    }

    /**
     * @covers Gitty\Repositories\AdapterAbstract::showBranches
     */
    public function testShowBranches()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);

        $repo->showBranches(true);
        $this->assertTrue($repo->showBranches());

        $repo->showBranches(false);
        $this->assertFalse($repo->showBranches());
    }

    /**
     * @covers Gitty\Repositories\AdapterAbstract::registerRemote
     * @covers Gitty\Repositories\AdapterAbstract::unregisterRemote
     * @covers Gitty\Repositories\AdapterAbstract::getRemotes
     */
    public function testRemotes()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);
        $this->assertEquals(0, \count($repo->getRemotes()));

        $remote_conf = new Gitty\Config(
            array(
                'hostname' => 'test',
                'username' => 'test',
                'password' => 'test'
            ),
            false,
            false
        );
        $remote = new Gitty\Remote($remote_conf);
        $repo->registerRemote($remote);
        $this->assertEquals(1, \count($repo->getRemotes()));
        $this->assertEquals($remote, \array_pop($repo->getRemotes()));

        $repo->unregisterRemote($remote);
        $this->assertEquals(0, \count($repo->getRemotes()));

        $this->assertFalse($repo->unregisterRemote($remote));
    }

    /**
     * @expectedException Gitty\Repositories\Exception
     * @covers Gitty\Repositories\Adapter\Git::__construct
     * @covers Gitty\Repositories\Adapter\Git::discoverGitDir
     */
    public function testDescriptionFileNotReadable()
    {
        $config = $this->config;

        $filename = $config->path.'/.git/description';
        $chmod = \substr(\sprintf('%o', \fileperms($filename)), -4);
        \chmod($filename, 0000);
        \clearstatcache();
        try {
            $repo = new Gitty\Repositories\Adapter\Git($config);
        } catch(Gitty\Repositories\Exception $e) {
            \chmod($filename, \octdec($chmod));
            throw $e;
        }

        \chmod($filename, \octdec($chmod));
    }

    /**
     * @covers Gitty\Repositories\Adapter\Git::__construct
     * @covers Gitty\Repositories\Adapter\Git::discoverGitDir
     */
    public function testDescriptionFileContents()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);

        $this->assertEquals(
            "Unnamed repository; edit this file 'description' to name the repository.",
            $repo->getDescription()
        );
    }

    /**
     * @covers Gitty\Repositories\Adapter\Git::getOwner
     * @covers Gitty\Repositories\Adapter\Git::execCommand
     */
    public function testOwner()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);

        $this->assertGreaterThan(0, \strlen($repo->getOwner()));
    }

    /**
     * @covers Gitty\Repositories\Adapter\Git::getLastChange
     * @covers Gitty\Repositories\Adapter\Git::setLastChange
     * @covers Gitty\Repositories\Adapter\Git::execCommand
     */
    public function testLastChange()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);

        $this->assertTrue($repo->getLastChange() instanceof \DateTime);

        $repo->setLastChange(new \DateTime());
        $this->assertTrue($repo->getLastChange() instanceof \DateTime);
    }

    /**
     * @covers Gitty\Repositories\Adapter\Git::setBranches
     * @covers Gitty\Repositories\Adapter\Git::getBranches
     */
    public function testBranches()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);

        $this->assertGreaterThan(0, \count($repo->getBranches()));
        $b_test = array('test', 'bar');
        $repo->setBranches($b_test);
        $this->assertEquals($repo->getBranches(), $b_test);

        $repo = new Gitty\Repositories\Adapter\Git($this->config);
        $repo->showBranches(false);
        $this->assertEquals($repo->getBranches(), array(array('name' => 'master', 'default' => 0)));
    }

    /**
     * @covers Gitty\Repositories\Adapter\Git::getInstallFiles
     */
    public function testInstall()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);
        $install = $repo->getInstallFiles();
        $this->assertGreaterThan(0, \count($install['added']));
        $this->assertEquals(
            $install['added'],
            array(
                'copy/file2.txt',
                'file1.txt',
                'file2.txt',
                'renamed.txt'
            )
        );
    }

    /**
     * @covers Gitty\Repositories\Adapter\Git::revId
     */
    public function testRevisionIds()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);

        $expected_rev_ids = array(
            'newest' => '5a910b78afcb2ff2522cc1ec62bb77048863a393',
            'oldest' => '43be268fde6a6d7401e76106e556b3e26487837a'
        );

        $this->assertEquals($repo->revId(), $expected_rev_ids);
        $this->assertEquals($repo->revId(true), $expected_rev_ids);
    }

    public function testGetRewestRevisitionId()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);
        $this->assertEquals(
            '5a910b78afcb2ff2522cc1ec62bb77048863a393',
            $repo->getNewestRevisitionId()
        );
    }

    /**
     * @covers Gitty\Repositories\Adapter\Git::setDefaultGitDir
     * @covers Gitty\Repositories\Adapter\Git::getDefaultGitDir
     */
    public function testDefaultGitDir()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);
        $uid = \uniqid();
        $repo->setDefaultGitDir($uid);
        $this->assertEquals($uid, $repo->getDefaultGitDir());
    }

    /**
     * @covers Gitty\Repositories\Adapter\Git::setDescriptionFile
     * @covers Gitty\Repositories\Adapter\Git::getDescriptionFile
     */
    public function testDescriptionFile()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);
        $uid = \uniqid();
        $repo->setDescriptionFile($uid);
        $this->assertEquals($uid, $repo->getDescriptionFile());
    }

    /**
     * @covers Gitty\Repositories\Adapter\Git::setBinLocation
     * @covers Gitty\Repositories\Adapter\Git::getBinLocation
     */
    public function testBinLocation()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);
        $uid = \uniqid();
        $repo->setBinLocation($uid);
        $this->assertEquals($uid, $repo->getBinLocation());
    }

    /**
     * @covers Gitty\Repositories\Adapter\Git::getUpdateFiles
     * @covers Gitty\Repositories\Adapter\Git::parseFiles
     */
    public function testUpdate()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);

        $repo->revId();
        $updates = $repo->getUpdateFiles('6cbdf356fb1f686ed4491d90981bae6aae5af600');
        $this->assertEquals(
            $updates,
            array(
                'added' => array(),
                'modified' => array('file1.txt'),
                'deleted' => array(),
                'copied' => array(),
                'renamed' => array()
            )
        );

        $updates = $repo->getUpdateFiles('d8ecaaee5a6d4a3c02b2e4dd424434bdfae12de2');
        $this->assertEquals(
            $updates,
            array(
                'added' => array('copy/file2.txt'),
                'modified' => array('file1.txt'),
                'deleted' => array(),
                'copied' => array(),
                'renamed' => array()
            )
        );

        $updates = $repo->getUpdateFiles('2aed3dbb1cb3f9567a4a9d9eb270cb3c3454f9ed');
        $this->assertEquals(
            $updates,
            array(
                'added' => array('copy/file2.txt'),
                'modified' => array('file1.txt'),
                'deleted' => array(),
                'copied' => array(),
                'renamed' => array(array('file3.txt' => 'renamed.txt'))
            )
        );

        $updates = $repo->getUpdateFiles('43be268fde6a6d7401e76106e556b3e26487837a');
        $this->assertEquals(
            $updates,
            array(
                'added' => array('copy/file2.txt'),
                'modified' => array('file1.txt'),
                'deleted' => array('file4.txt'),
                'copied' => array(),
                'renamed' => array(array('file3.txt' => 'renamed.txt'))
            )
        );

        $this->markTestIncomplete(
            'add tests for copied files'
        );
    }

    /**
     * @covers Gitty\Repositories\Adapter\Git::getInstallFiles
     * @covers Gitty\Repositories\Adapter\Git::getFile
     */
    public function testGetFile()
    {
        $repo = new Gitty\Repositories\Adapter\Git($this->config);

        $install = $repo->getInstallFiles();
        $files = $install['added'];

        foreach ($files as $file) {
            $handle = $repo->getFile($file);
            $this->assertTrue(\is_resource($handle));
            \fclose($handle);
        }
    }
}
