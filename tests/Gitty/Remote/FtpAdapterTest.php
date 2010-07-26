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

class FtpAdapterTest extends \PHPUnit_Framework_TestCase
{
    protected $connectionRemote = null;

    // set javascriptq and lubyte to your own
    protected $projectName = 'javascriptq';
    protected $remoteName = 'lubyte';
    protected $workingConfig = '/../../../config-homebox.ini';

    protected function getConnectionRemote()
    {
        if (null === $this->connectionRemote) {
            // set configuration file with full functional ftp
            $config = new Gitty\Config\Ini(\dirname(__FILE__).$this->workingConfig);
            $remote = new Gitty\Remote($config->projects->{$this->projectName}->deployment->{$this->remoteName});
            $this->connectionRemote = $remote;
        }

        return $this->connectionRemote;
    }

    public function testClose()
    {
        // set configuration file with full functional ftp
        $config = new Gitty\Config\Ini(\dirname(__FILE__).$this->workingConfig);

        $config->projects->{$this->projectName}->deployment->{$this->remoteName}->port = 21;
        $config->projects->{$this->projectName}->deployment->{$this->remoteName}->revisitionFileName = 'myRevFile.txt';
        $remote = new Gitty\Remote\Adapter\Ftp($config->projects->{$this->projectName}->deployment->{$this->remoteName});
        $remote->close();
    }

    /**
     * @expectedException Gitty\Remote\Exception
     */
    public function testInvalidHostName()
    {
        // set configuration file with full functional ftp
        $config = new Gitty\Config\Ini(\dirname(__FILE__).$this->workingConfig);

        $config->projects->{$this->projectName}->deployment->{$this->remoteName}->hostname = \uniqid().'______.local';
        $remote = new Gitty\Remote\Adapter\Ftp($config->projects->{$this->projectName}->deployment->{$this->remoteName});
        $remote->init();
        $remote->close();
    }

    /**
     * @expectedException Gitty\Remote\Exception
     */
    public function testInvalidLogin()
    {
        // set configuration file with full functional ftp
        $config = new Gitty\Config\Ini(\dirname(__FILE__).$this->workingConfig);

        $config->projects->{$this->projectName}->deployment->{$this->remoteName}->hostname = 'ftp.mozilla.org';
        $config->projects->{$this->projectName}->deployment->{$this->remoteName}->username = 'user_'.\uniqid();
        $config->projects->{$this->projectName}->deployment->{$this->remoteName}->password = 'pass_'.\uniqid();
        $remote = new Gitty\Remote\Adapter\Ftp($config->projects->{$this->projectName}->deployment->{$this->remoteName});
        $remote->init();
        $remote->close();
    }

    public function testGetServerRevisitionId()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).$this->workingConfig);

        $config->projects->{$this->projectName}->deployment->{$this->remoteName}->revisitionFileName = 'myRevFile.txt';
        $remote = new Gitty\Remote\Adapter\Ftp($config->projects->{$this->projectName}->deployment->{$this->remoteName});
        $this->assertEquals($remote->getServerRevisitionId(), null);
        $remote->close();
    }

    public function testCopyUnknownFile()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).$this->workingConfig);

        $remote = new Gitty\Remote\Adapter\Ftp($config->projects->{$this->projectName}->deployment->{$this->remoteName});
        $remote->init();
        $this->assertEquals($remote->copy(\uniqid().'/unknown.txt', '/foo/bar'), null);
        $remote->close();
    }

    public function testGetAdapterName()
    {
        $config = new Gitty\Config\Ini(\dirname(__FILE__).$this->workingConfig);
        $remote = new Gitty\Remote\Adapter\Ftp($config->projects->{$this->projectName}->deployment->{$this->remoteName});
        $this->assertEquals($remote->getAdapterName(), 'Ftp');
        $remote->close();
    }
}
