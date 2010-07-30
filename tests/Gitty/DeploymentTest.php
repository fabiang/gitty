<?php
namespace Gitty\Tests\Gitty;

use \Gitty as Gitty;

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/TestCase.php';

require_once \dirname(__FILE__).'/../../library/Gitty/Version.php';

/**
 * @outputBuffering disabled
 */
class DeploymentTest extends \PHPUnit_Framework_TestCase
{
    protected $config = null;

    public function setUp()
    {
        $config = new Gitty\Config\Ini(dirname(__FILE__).'/../data/workingExample.ini');
        $config->projects->myproject->path = \realpath(\dirname(__FILE__).'/../data/example');
        $this->config = $config;
    }
    public function tearDown()
    {
        $this->config = null;
    }

    /**
     * @covers Gitty\Deployment::getObservers
     * @covers Gitty\Deployment::registerObserver
     * @covers Gitty\Deployment::unregisterObserver
     */
    public function testObservers()
    {
        $deploy = new Gitty\Deployment($this->config);

        $observer1 = new Gitty\Observer\Html();
        $observer2 = new Gitty\Observer\Html();
        $observer = $deploy->getObservers();
        $this->assertEquals(array(), $observer);

        $deploy->registerObserver($observer1);
        $observer = $deploy->getObservers();
        $this->assertEquals(array($observer1), $observer);

        $deploy->registerObserver($observer2);
        $observer = $deploy->getObservers();
        $this->assertEquals(array($observer1, $observer2), $observer);

        $deploy->unregisterObserver($observer1);
        $observer = $deploy->getObservers();
        $this->assertEquals(array(1 => $observer2), $observer);

        $deploy->unregisterObserver($observer2);
        $observer = $deploy->getObservers();
        $this->assertEquals(array(), $observer);

        $observer3 = new Gitty\Observer\Html();
        $this->assertFalse($deploy->unregisterObserver($observer3));
    }

    /**
     * @covers Gitty\Deployment::__construct
     * @covers Gitty\Deployment::__destruct
     */
    public function testConstructDestruct()
    {
        $deploy = new Gitty\Deployment($this->config);
        unset($deploy);
    }

    /**
     * @covers Gitty\Deployment::setProjectId
     * @covers Gitty\Deployment::getProjectId
     * @covers Gitty\Deployment::getCurrentRepository
     */
    public function testProjectId()
    {
        $deploy = new Gitty\Deployment($this->config);

        $deploy->setProjectId(0);
        $this->assertEquals(0, $deploy->getProjectId());

        $deploy->setProjectId('test');
        $this->assertEquals(0, $deploy->getProjectId());
    }

    /**
     * @covers Gitty\Deployment::setBranch
     * @covers Gitty\Deployment::getBranch
     */
    public function testBranch()
    {
        $deploy = new Gitty\Deployment($this->config);

        $deploy->setBranch('test');
        $this->assertEquals('test', $deploy->getBranch());
    }

    /**
     * @covers Gitty\Deployment::setRemoteId
     * @covers Gitty\Deployment::getRemoteId
     * @covers Gitty\Deployment::getCurrentRemote
     */
    public function testRemoteId()
    {
        $deploy = new Gitty\Deployment($this->config);

        $deploy->setRemoteId(0);
        $this->assertEquals(0, $deploy->getRemoteId());

        $deploy->setRemoteId('test');
        $this->assertEquals(0, $deploy->getRemoteId());
    }

    /**
     * @expectedException Gitty\Exception
     * @covers Gitty\Deployment::setProjectId
     * @covers Gitty\Deployment::getCurrentRepository
     */
    public function testProjectIdIsNotRegistered()
    {
        $deploy = new Gitty\Deployment($this->config);
        $deploy->setProjectId(1000);
    }

    /**
     * @expectedException Gitty\Exception
     * @covers Gitty\Deployment::setRemoteId
     * @covers Gitty\Deployment::getCurrentRemote
     */
    public function testRemoteIdIsNotRegistered()
    {
        $deploy = new Gitty\Deployment($this->config);
        $deploy->setRemoteId(1000);
    }

    /**
     * @covers Gitty\Deployment::start
     * @covers Gitty\Deployment::end
     * @covers Gitty\Deployment::getFiles
     * @covers Gitty\Deployment::writeRevistionFile
     * @covers Gitty\Deployment::callObservers
     */
    public function testDeploymentInstall()
    {
        $deploy = new Gitty\Deployment($this->config, true);
        $deploy->setProjectId(0);
        $deploy->setRemoteId(0);
        $deploy->setBranch('master');
        $deploy->registerObserver(new Gitty\Observer\Html());
        \ob_start();
        \ob_start();
        $deploy->start();
        $deploy->end();
        \ob_end_flush();
        \ob_end_clean();
    }

    /**
     * @covers Gitty\Deployment::start
     * @covers Gitty\Deployment::end
     * @covers Gitty\Deployment::getFiles
     * @covers Gitty\Deployment::writeRevistionFile
     * @covers Gitty\Deployment::callObservers
     */
    public function testDeploymentUpdate()
    {
        $deploy = new Gitty\Deployment($this->config);
        $deploy->setProjectId(0);
        $deploy->setRemoteId(0);
        $deploy->setBranch('master');
        $deploy->registerObserver(new Gitty\Observer\Html());

        $remote = $deploy->getCurrentRemote();
        $rev_ids = array(
            '6cbdf356fb1f686ed4491d90981bae6aae5af600',
            'd8ecaaee5a6d4a3c02b2e4dd424434bdfae12de2',
            '2aed3dbb1cb3f9567a4a9d9eb270cb3c3454f9ed',
            '43be268fde6a6d7401e76106e556b3e26487837a'
        );
        \ob_start();
        \ob_start();

        foreach ($rev_ids as $rev_id) {
            $remote->putServerRevisitionId($rev_id);
            $deploy->start();
            $deploy->end();
        }

        //should be up-to-date
        $deploy->start();
        $deploy->end();
        \ob_end_flush();
        \ob_end_clean();
    }
}
