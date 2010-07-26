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

class RepositoriesTest extends \PHPUnit_Framework_TestCase
{
    public function testSetDefaultAdapter()
    {
        Gitty\Repositories::getDefaultAdapter();

        $adapter = 'Gitty\\Repositories\\Adapter\\Git';
        Gitty\Repositories::setDefaultAdapter($adapter);

        $this->assertEquals(Gitty\Repositories::getDefaultAdapter(), $adapter);
    }

    /**
     * @expectedException Gitty\Repositories\Exception
     */
    public function testSetDefaultUnknownAdapter()
    {
        $adapter = 'Gitty\\Version';
        Gitty\Repositories::setDefaultAdapter($adapter);
    }

    public function testAdapterLoading()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../data/example.ini');
        $repo = new Gitty\Repositories($config);

        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../data/knownAdapter.ini');
        $repo = new Gitty\Repositories($config);
    }

    /**
     * @expectedException Gitty\Repositories\Exception
     */
    public function testUnknownAdapter()
    {
        $config= new Gitty\Config\Ini(\dirname(__FILE__).'/../data/unknownAdapter.ini');
        $repo = new Gitty\Repositories($config);
    }

    public function testRepositoryRegister()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).'/../data/knownAdapter.ini');
        $repo = new Gitty\Repositories($config);

        $git = new Gitty\Repositories\Adapter\Git($config->projects->myproject);

        $this->assertEquals($repo->getRepositories(), array($git));

        $repo->register($git);
        $this->assertTrue($repo->unregister($git));
        $first = \array_shift($repo->getRepositories());
        $this->assertTrue($repo->unregister($first));
        $this->assertEquals($repo->getRepositories(), array());

        $otherGit = new Gitty\Repositories\Adapter\Git($config->projects->myproject);
        $this->assertFalse($repo->unregister($otherGit));
    }
}
