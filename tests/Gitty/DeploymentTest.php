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
    public function testObservers()
    {
        $config = new Gitty\Config\Ini(dirname(__FILE__).'/../data/example.ini');
        $deploy = new Gitty\Deployment($config);

        $observer1 = new Gitty\Observer\Html();
        $observer2 = new Gitty\Observer\Html();
        $observer = $deploy->getObservers();
        $this->assertEquals($observer, array());

        $deploy->registerObserver($observer1);
        $observer = $deploy->getObservers();
        $this->assertEquals($observer, array($observer1));

        $deploy->registerObserver($observer2);
        $observer = $deploy->getObservers();
        $this->assertEquals($observer, array($observer1, $observer2));

        $deploy->unregisterObserver($observer1);
        $observer = $deploy->getObservers();
        $this->assertEquals($observer, array(1 => $observer2));

        $deploy->unregisterObserver($observer2);
        $observer = $deploy->getObservers();
        $this->assertEquals($observer, array());

        $observer3 = new Gitty\Observer\Html();
        $this->assertFalse($deploy->unregisterObserver($observer3));
    }

    public function testProjectId()
    {
        $config = new Gitty\Config\Ini(dirname(__FILE__).'/../data/example.ini');
        $deploy = new Gitty\Deployment($config);

        $deploy->setProjectId(0);
        $this->assertEquals(0, $deploy->getProjectId());

        $deploy->setProjectId('test');
        $this->assertEquals(0, $deploy->getProjectId());
    }

    public function testBranch()
    {
        $config = new Gitty\Config\Ini(dirname(__FILE__).'/../data/example.ini');
        $deploy = new Gitty\Deployment($config);

        $deploy->setBranch('test');
        $this->assertEquals('test', $deploy->getBranch());
    }

    public function testRemoteId()
    {
        $config = new Gitty\Config\Ini(dirname(__FILE__).'/../data/example.ini');
        $deploy = new Gitty\Deployment($config);

        $deploy->setRemoteId(0);
        $this->assertEquals(0, $deploy->getRemoteId());

        $deploy->setRemoteId('test');
        $this->assertEquals(0, $deploy->getRemoteId());
    }

    /**
     * @expectedException Gitty\Exception
     */
    public function testProjectIdIsNotRegistered()
    {
        $config = new Gitty\Config\Ini(dirname(__FILE__).'/../data/example.ini');
        $deploy = new Gitty\Deployment($config);

        $deploy->setProjectId(1000);
    }

    /**
     * @expectedException Gitty\Exception
     */
    public function testRemoteIdIsNotRegistered()
    {
        $config = new Gitty\Config\Ini(dirname(__FILE__).'/../data/example.ini');
        $deploy = new Gitty\Deployment($config);

        $deploy->setRemoteId(1000);
    }
}
