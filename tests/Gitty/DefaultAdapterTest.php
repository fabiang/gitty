<?php
namespace Gitty\Tests\Gitty;

use \Gitty as Gitty;

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/TestCase.php';

require_once \dirname(__FILE__).'/../../library/Gitty/Config.php';
require_once \dirname(__FILE__).'/../../library/Gitty/Config/Ini.php';
require_once \dirname(__FILE__).'/../../library/Gitty/Repositories/Exception.php';
require_once \dirname(__FILE__).'/../../library/Gitty/Remote/Exception.php';
require_once \dirname(__FILE__).'/../../library/Gitty/Repositories.php';
require_once \dirname(__FILE__).'/../../library/Gitty/Remote.php';

class DefaultAdapterTest extends \PHPUnit_Framework_TestCase
{
    protected $repoDefaultAdapter = null;
    protected $remoteDefaultAdapter = null;
    protected $includePath = null;

    public function setUp()
    {
        $this->repoDefaultAdapter = Gitty\Repositories::getDefaultAdapter();
        $this->remoteDefaultAdapter = Gitty\Remote::getDefaultAdapter();

        $this->includePath = \get_include_path();
        \set_include_path(
            $this->includePath . \PATH_SEPARATOR . \dirname(__FILE__) . '/../data/'
        );
    }

    public function tearDown()
    {
        Gitty\Repositories::setDefaultAdapter($this->repoDefaultAdapter);
        Gitty\Remote::setDefaultAdapter($this->remoteDefaultAdapter);
        \set_include_path($this->includePath);
    }

    /**
     * @dataProvider provideDefaultAdapters
     * @covers Gitty\Remote::setDefaultAdapter
     * @covers Gitty\Remote::getDefaultAdapter
     * @covers Gitty\Repositories::setDefaultAdapter
     * @covers Gitty\Repositories::getDefaultAdapter
     */
    public function testSetDefaultAdapter($class, $adapter)
    {
        $class::setDefaultAdapter($adapter);
        $this->assertEquals($adapter, $class::getDefaultAdapter());
    }

    /**
     * @dataProvider provideDefaultAdapterClassInvalidData
     * @covers Gitty\Remote::__construct
     * @covers Gitty\Remote\Exception
     * @covers Gitty\Repositories::__construct
     * @covers Gitty\Repositories\Exception
     */
    public function testDefaultAdapterClassNotFound($class, $unkown_class, $config, $exception)
    {
        $this->setExpectedException($exception);
        $class::setDefaultAdapter($unkown_class);
        $myClass = new $class($config);
    }

    /**
     * @dataProvider provideDefaultAdapterClassInvalidData
     * @covers Gitty\Remote::__construct
     * @covers Gitty\Remote\Exception
     * @covers Gitty\Repositories::__construct
     * @covers Gitty\Repositories\Exception
     */
    public function testDefaultAdapterClassInvalid($class, $invalid_class, $config, $exception)
    {
        $this->setExpectedException($exception);
        $class::setDefaultAdapter($invalid_class);
        $myClass = new $class($config);
    }

    /**
     * @dataProvider provideDefaultAdapterLoadingData
     * @covers Gitty\Remote::__construct
     * @covers Gitty\Repositories::__construct
     */
    public function testDefaultAdapterLoading($class, $config, $getadapter)
    {
        $myClass = new $class($config);
        $default_adapter = $class::getDefaultAdapter();
        $this->assertEquals(\get_class($getadapter()), $default_adapter);
    }

    /**
     * @dataProvider provideUnknownAdapterData
     * @covers Gitty\Remote::__construct
     * @covers Gitty\Remote\Exception
     * @covers Gitty\Repositories::__construct
     * @covers Gitty\Repositories\Exception
     */
    public function testUnknownAdapter($class, $config, $exception)
    {
        $this->setExpectedException($exception);
        $class::setDefaultAdapter('MyNamespace\\Test\\Foo');
        $myClass = new $class($config);
    }

    /**
     * @dataProvider provideInvalidAdapterData
     * @covers Gitty\Remote::__construct
     * @covers Gitty\Remote\Exception
     * @covers Gitty\Repositories::__construct
     * @covers Gitty\Repositories\Exception
     */
    public function testInvalidAdapter($class, $namespace, $config, $exception)
    {
        $this->setExpectedException($exception);
        $class::registerAdapterNamespace($namespace);
        try {
            $myClass = new $class($config);
        } catch(\Exception $e) {
            $class::unregisterAdapterNamespace($namespace);
            throw $e;
        }

        $class::unregisterAdapterNamespace($namespace);
    }

    /**
     * data providers
     */
    public static function provideDefaultAdapters()
    {
        return array(
            array('Gitty\\Remote', 'Gitty\\Remote\\Adapter\\Foobar'),
            array('Gitty\\Repositories', 'Gitty\\Repositories\\Adapter\\Foobar')
        );
    }

    public static function provideDefaultAdapterClassNotFoundData()
    {
        $remote_config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/remoteUnknownAdapter.ini'
        );
        $remote_config = $remote_config->projects->myproject->deployment->hostnamecom;

        $repo_config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/unknownAdapter.ini'
        );

        return array(
            array(
                'Gitty\\Remote',
                'Gitty\Version'.\uniqid(),
                $remote_config,
                'Gitty\\Remote\\Exception'
            ),
            array(
                'Gitty\\Repositories',
                'Gitty\Version'.\uniqid(),
                $repo_config,
                'Gitty\\Repositories\\Exception'
            )
        );
    }

    public static function provideDefaultAdapterClassInvalidData()
    {
        $remote_config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/remoteUnknownAdapter.ini'
        );
        $remote_config = $remote_config->projects->myproject->deployment->hostnamecom;

        $repo_config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/unknownAdapter.ini'
        );

        return array(
            array(
                'Gitty\\Remote',
                'ExampleNamespace\\Gitty\\Remote\\Adapter\\Invalid',
                $remote_config,
                'Gitty\\Remote\\Exception'
            ),
            array(
                'Gitty\\Repositories',
                'ExampleNamespace\\Gitty\\Repositories\\Adapter\\Invalid',
                $repo_config,
                'Gitty\\Repositories\\Exception'
            )
        );
    }

    public static function provideDefaultAdapterLoadingData()
    {
        $remote_config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/emptyAdapter.ini'
        );
        $remote_config = $remote_config->projects->myproject->deployment->hostnamecom;

        $repo_config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/emptyAdapter.ini'
        );

        return array(
            array(
                'Gitty\\Remote',
                $remote_config,
                function () use($remote_config) {
                    $r = new Gitty\Remote($remote_config);
                    return $r->getAdapter();
                }
            ),
            array(
                'Gitty\\Repositories',
                $repo_config,
                function () use($repo_config) {
                    $r = new Gitty\Repositories($repo_config);
                    $repos = $r->getRepositories();
                    return $repos[0];
                }
            )
        );
    }

    public static function provideUnknownAdapterData()
    {
        $remote_config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/emptyAdapter.ini'
        );
        $remote_config = $remote_config->projects->myproject->deployment->hostnamecom;

        $repo_config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/emptyAdapter.ini'
        );

        return array(
            array(
                'Gitty\\Remote',
                $remote_config,
                'Gitty\\Remote\\Exception'
            ),
            array(
                'Gitty\\Repositories',
                $repo_config,
                'Gitty\\Repositories\\Exception'
            ),
        );
    }

    public static function provideInvalidAdapterData()
    {
        $remote_config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/invalidAdapter.ini'
        );
        $remote_config = $remote_config->projects->myproject->deployment->hostnamecom;

        $repo_config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/invalidAdapter.ini'
        );

        return array(
            array(
                'Gitty\\Remote',
                'ExampleNamespace\\Gitty\\Remote\\Adapter',
                $remote_config,
                'Gitty\\Remote\\Exception'
            ),
            array(
                'Gitty\\Repositories',
                'ExampleNamespace\\Gitty\\Repositories\\Adapter',
                $repo_config,
                'Gitty\\Repositories\\Exception'
            ),
        );
    }
}
