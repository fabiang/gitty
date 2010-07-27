<?php
namespace Gitty\Tests\Gitty\Observer;

use \Gitty as Gitty;

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/TestCase.php';

require_once \dirname(__FILE__).'/../../../library/Gitty/Observer/ObserverInterface.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Observer/Html.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Deployment.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Config.php';
require_once \dirname(__FILE__).'/../../../library/Gitty/Config/Ini.php';

class HtmlObserverTest extends \PHPUnit_Framework_TestCase
{
    protected $workingConfig = '../../../config-homebox.ini';
    protected $projectName = 'javascriptq';
    protected $remoteName = 'lubyte';

    protected function getConf()
    {
        $config1 = new Gitty\Config\Ini(dirname(__FILE__).'/'.$this->workingConfig);
        $config2 = new Gitty\Config\Ini(dirname(__FILE__).'/../../data/example.ini');

        $dep1 = $config1->projects->{$this->projectName}->deployment->{$this->remoteName};

        $config2->projects->myproject->deployment->hostnamecom->hostname = $dep1->hostname;
        if ($dep1->port) {
            $config2->projects->myproject->deployment->hostnamecom->port = $dep1->port;
        }
        $config2->projects->myproject->deployment->hostnamecom->username = $dep1->username;
        $config2->projects->myproject->deployment->hostnamecom->password = $dep1->password;
        $config2->projects->myproject->deployment->hostnamecom->path = $dep1->path;
        $config2->projects->myproject->path = \realpath(\dirname(__FILE__).'/../../data/example');

        return $config2;
    }

    public function testObserverUpdate()
    {
        $config = $this->getConf();

        $deploy = new Gitty\Deployment($config);

        $deploy->registerObserver(new Gitty\Observer\Html());
        $deploy->setProjectId(0);
        $deploy->setRemoteId(0);
        $deploy->setBranch('master');

        $remote = $deploy->getCurrentRemote();

        \ob_start();
        \ob_start();
        $remote->putServerRevisitionId('6cbdf356fb1f686ed4491d90981bae6aae5af600');
        $deploy->start();
        $deploy->end();

        $remote->putServerRevisitionId('d8ecaaee5a6d4a3c02b2e4dd424434bdfae12de2');
        $deploy->start();
        $deploy->end();

        $remote->putServerRevisitionId('2aed3dbb1cb3f9567a4a9d9eb270cb3c3454f9ed');
        $deploy->start();
        $deploy->end();

        $remote->putServerRevisitionId('43be268fde6a6d7401e76106e556b3e26487837a');
        $deploy->start();
        $deploy->end();

        //should be up-to-date
        $deploy->start();
        $deploy->end();
        \ob_end_flush();
        \ob_end_clean();
    }

    public function testObserverInstall()
    {
        $config = $this->getConf();

        $deploy = new Gitty\Deployment($config, true);

        $deploy->registerObserver(new Gitty\Observer\Html());
        $deploy->setProjectId(0);
        $deploy->setRemoteId(0);
        $deploy->setBranch('master');

        \ob_start();
        \ob_start();
        $deploy->start();
        $deploy->end();
        \ob_end_flush();
        \ob_end_clean();
    }
}
