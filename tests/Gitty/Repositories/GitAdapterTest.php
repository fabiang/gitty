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
    public function testAdapterOptions()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');

        $repo = new Gitty\Repositories($config);
        $repo_adapter = \array_shift($repo->getRepositories());

        $options = array('name', 'description', 'path', 'owner');
        foreach ($options as $option) {
            $function_set = 'set'.ucfirst($option);
            $function_get = 'get'.ucfirst($option);
            $repo_adapter->$function_set('test');
            $this->assertEquals($repo_adapter->$function_get(), 'test');
        }

        foreach ($options as $option) {
            $repo_adapter->$option = 'test';
            $this->assertEquals($repo_adapter->$option, 'test');
        }
    }

    /**
     * @expectedException Gitty\Repositories\Exception
     */
    public function testSetUnkownOption()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');

        $repo = new Gitty\Repositories($config);
        $repo_adapter = \array_shift($repo->getRepositories());

        $u = \uniqid();
        $repo_adapter->$u = 'test';
    }

    /**
     * @expectedException Gitty\Repositories\Exception
     */
    public function testGetUnkownOption()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');

        $repo = new Gitty\Repositories($config);
        $repo_adapter = \array_shift($repo->getRepositories());

        $u = \uniqid();
        $foo = $repo_adapter->$u;
    }

    public function testGetSetOptions()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');

        $repo = new Gitty\Repositories($config);
        $repo_adapter = \array_shift($repo->getRepositories());

        $options = $repo_adapter->getOptions();
        $this->assertEquals($options, $repo_adapter->toArray());

        foreach ($options as $option_name => $option) {
            $func = 'get'. \ucfirst($option_name);
            $this->assertEquals($option, $repo_adapter->$func());
        }

        $repo_adapter->setOptions($options);
        $this->assertEquals($repo_adapter->getOptions(), $options);
    }

    public function testSetPath()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');

        $repo = new Gitty\Repositories($config);
        $repo_adapter = \array_shift($repo->getRepositories());

        $repo_adapter->setPath('/foo/bar/');
        $this->assertEquals($repo_adapter->getPath(), '/foo/bar');
    }

    public function testAdapterName()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');

        $repo = new Gitty\Repositories($config);
        $repo_adapter = \array_shift($repo->getRepositories());

        $this->assertEquals($repo_adapter->getAdapterName(), 'Git');
    }

    public function testShowBranches()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');

        $repo = new Gitty\Repositories($config);
        $repo_adapter = \array_shift($repo->getRepositories());

        $repo_adapter->showBranches(true);
        $this->assertTrue($repo_adapter->showBranches());

        $repo_adapter->showBranches(false);
        $this->assertFalse($repo_adapter->showBranches());
    }

    public function testRemotes()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');

        $repo = new Gitty\Repositories($config);
        $repo_adapter = \array_shift($repo->getRepositories());

        $this->assertEquals(\count($repo_adapter->getRemotes()), 1);

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
        $repo_adapter->registerRemote($remote);
        $this->assertEquals(\count($repo_adapter->getRemotes()), 2);
        $this->assertEquals(\array_pop($repo_adapter->getRemotes()), $remote);

        $repo_adapter->unregisterRemote($remote);
        $this->assertEquals(\count($repo_adapter->getRemotes()), 1);

        $this->assertFalse($repo_adapter->unregisterRemote($remote));
    }

    /**
     * @expectedException Gitty\Repositories\Exception
     */
    public function testDescriptionFileNotReadable()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');
        $filename = $config->projects->myproject->path.'/.git/description';
        $chmod = \substr(\sprintf('%o', \fileperms($filename)), -4);
        \chmod($filename, 0000);
        \clearstatcache();
        try {
            $repo = new Gitty\Repositories\Adapter\Git($config->projects->myproject);
        } catch(Gitty\Repositories\Exception $e) {
            \chmod($filename, \octdec($chmod));
            throw $e;
        }

        \chmod($filename, \octdec($chmod));
    }

    public function testOwner()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');
        $repo = new Gitty\Repositories\Adapter\Git($config->projects->myproject);

        $this->assertGreaterThan(0, \strlen($repo->getOwner()));
    }

    public function testLastChange()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');
        $repo = new Gitty\Repositories\Adapter\Git($config->projects->myproject);

        $this->assertTrue($repo->getLastChange() instanceof \DateTime);

        $repo->setLastChange(new \DateTime());
        $this->assertTrue($repo->getLastChange() instanceof \DateTime);
    }

    public function testBranches()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');
        $repo = new Gitty\Repositories\Adapter\Git($config->projects->myproject);

        $this->assertGreaterThan(0, \count($repo->getBranches()));
        $b_test = array('test', 'bar');
        $repo->setBranches($b_test);
        $this->assertEquals($repo->getBranches(), $b_test);

        $repo = new Gitty\Repositories\Adapter\Git($config->projects->myproject);
        $repo->showBranches(false);
        $this->assertEquals($repo->getBranches(), array(array('name' => 'master', 'default' => 0)));
    }

    public function testInstall()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');
        $repo = new Gitty\Repositories\Adapter\Git($config->projects->myproject);

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

    public function testRevisionIds()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');
        $repo = new Gitty\Repositories\Adapter\Git($config->projects->myproject);

        $revIds = $repo->revId();
        $this->assertEquals(
            $revIds,
            array(
                'newest' => '5a910b78afcb2ff2522cc1ec62bb77048863a393',
                'oldest' => '43be268fde6a6d7401e76106e556b3e26487837a'
            )
        );
    }

    public function testUpdate()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');
        $repo = new Gitty\Repositories\Adapter\Git($config->projects->myproject);

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
    }

    public function testGetFile()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../../data/example.ini');
        $repo = new Gitty\Repositories\Adapter\Git($config->projects->myproject);

        $install = $repo->getInstallFiles();
        $files = $install['added'];

        foreach ($files as $file) {
            $handle = $repo->getFile($file);
            $this->assertTrue(\is_resource($handle));
            \fclose($handle);
        }
    }
}
