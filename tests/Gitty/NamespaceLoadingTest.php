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

class NamespaceLoadingTest extends \PHPUnit_Framework_TestCase
{
    protected $remoteNs = array();
    protected $repoNs = array();

    public function setUp()
    {
        $this->remoteNs = Gitty\Remote::getAdapterNamespaces();
        $this->repoNs = Gitty\Repositories::getAdapterNamespaces();
    }

    public function tearDown()
    {
        foreach (Gitty\Remote::getAdapterNamespaces() as $adapterNs) {
            if (!\in_array($adapterNs, $this->remoteNs)) {
                Gitty\Remote::unregisterAdapterNamespace($adapterNs);
            }
        }

        foreach (Gitty\Repositories::getAdapterNamespaces() as $adapterNs) {
            if (!\in_array($adapterNs, $this->repoNs)) {
                Gitty\Repositories::unregisterAdapterNamespace($adapterNs);
            }
        }
    }

    /**
     * @dataProvider provideTestNamespaceRegisterData
     * @covers Gitty\Remote::registerAdapterNamespace
     * @covers Gitty\Remote::unregisterAdapterNamespace
     * @covers Gitty\Remote::getAdapterNamespaces
     * @covers Gitty\Repositories::registerAdapterNamespace
     * @covers Gitty\Repositories::unregisterAdapterNamespace
     * @covers Gitty\Repositories::getAdapterNamespaces
     */
    public function testNamespaceRegister($class, $test_ns)
    {
        $this->assertTrue($class::registerAdapterNamespace($test_ns));
        $this->assertEquals(array($test_ns), $class::getAdapterNamespaces());
        $this->assertFalse($class::registerAdapterNamespace($test_ns));
        $this->assertEquals(array($test_ns), $class::getAdapterNamespaces());

        $this->assertTrue($class::unregisterAdapterNamespace($test_ns));
        $this->assertEquals(array(), $class::getAdapterNamespaces());
        $this->assertFalse($class::unregisterAdapterNamespace($test_ns));
    }

    /**
     * @dataProvider provideTestNamespaceLoadingData
     * @covers Gitty\Remote::__construct
     * @covers Gitty\Repositories::__construct
     */
    public function testAdapterLoadingFromNamespace($class, $file, $namespaces, $config)
    {
        include_once $file;
        foreach ($namespaces as $namespace) {
            $class::registerAdapterNamespace($namespace);
        }

        $remote = new $class($config);

        foreach ($namespaces as $namespace) {
            $class::unregisterAdapterNamespace($namespace);
        }
    }

    /**
     * @dataProvider provideTestNamespaceLoadingUnkownData
     * @covers Gitty\Exception
     * @covers Gitty\Remote::__construct
     * @covers Gitty\Remote\Exception
     * @covers Gitty\Repositories::__construct
     * @covers Gitty\Repositories\Exception
     */
    public function testAdapterLoadingFromNamespaceUnknown($class, $namespaces, $config, $exception)
    {
        $this->setExpectedException($exception);

        foreach ($namespaces as $namespace) {
            $class::registerAdapterNamespace($namespace);
        }

        try {
            $remote = new $class($config);
        } catch (\Exception $e) {
            foreach ($namespaces as $namespace) {
               $class::unregisterAdapterNamespace($namespace);
            }
            throw $e;
        }

        foreach ($namespaces as $namespace) {
            $class::unregisterAdapterNamespace($namespace);
        }
    }

    /**
     * @dataProvider provideTestNamespaceLoadingInvalidData
     * @covers Gitty\Remote::__construct
     * @covers Gitty\Remote\Exception
     * @covers Gitty\Repositories::__construct
     * @covers Gitty\Repositories\Exception
     */
    public function testAdapterLoadingFromNamespaceInvalid($class, $file, $namespace, $config, $exception)
    {
        $this->setExpectedException($exception);

        include $file;
        $class::registerAdapterNamespace($namespace);

        try {
            $remote = new $class($config);
        } catch(\Exception $e) {
            $class::unregisterAdapterNamespace($namespace);
            throw $e;
        }

        $class::unregisterAdapterNamespace($namespace);
    }

    /**
     * data providers
     */
    public static function provideTestNamespaceRegisterData()
    {
        return array(
            array('Gitty\\Remote', 'MyNamespace\\Gitty\\Remote\\Foobar'),
            array('Gitty\\Remote', 'MyNamespace\\Gitty\\Kaboom'),
            array('Gitty\\Repositories', 'MyNamespace\\Gitty\\Repositories\\Foobar'),
            array('Gitty\\Repositories', 'MyNamespace\\Gitty\\Kaboom')
        );
    }

    public static function provideTestNamespaceLoadingData()
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
                'ExampleNamespace/Gitty/Remote/Adapter/Foobar.php',
                array(
                    'ExampleNamespace\\Unknown',
                    'ExampleNamespace\\Gitty\\Remote\\Adapter',
                ),
                $remote_config
            ),
            array(
                'Gitty\\Remote',
                'ExampleNamespace/Gitty/Repositories/Adapter/SomeUnkownAdapter.php',
                array(
                    'ExampleNamespace\\Unknown2',
                    'ExampleNamespace\\Gitty\\Repositories\\Adapter',
                ),
                $repo_config
            )
        );
    }

    public static function provideTestNamespaceLoadingUnkownData()
    {
        $remote_config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/remoteUnknownAdapter.ini'
        );
        $remote_config = $remote_config->projects->myproject->deployment->hostnamecom;
        $remote_config->adapter = \uniqid();

        $repo_config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/unknownAdapter.ini'
        );
        $repo_config->projects->myproject->adapter = \uniqid();

        return array(
            array(
                'Gitty\\Remote',
                array(
                    'ExampleNamespace\\Foobar',
                    'ExampleNamespace\\Gitty\\Remote\\Adapter'
                ),
                $remote_config,
                'Gitty\Remote\Exception'
            ),
            array(
                'Gitty\\Repositories',
                array(
                    'ExampleNamespace\\Foobar',
                    'ExampleNamespace\\Gitty\\Repositories\\Adapter'
                ),
                $repo_config,
                'Gitty\Repositories\Exception'
            )
        );
    }

    public static function provideTestNamespaceLoadingInvalidData()
    {
        $remote_config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/remoteUnknownAdapter.ini'
        );
        $remote_config = $remote_config->projects->myproject->deployment->hostnamecom;
        $remote_config->adapter = 'Invalid';

        $repo_config = new Gitty\Config\Ini(
            \dirname(__FILE__).'/../data/unknownAdapter.ini'
        );
        $repo_config->projects->adapter = 'Invalid';

        return array(
            array(
                'Gitty\\Remote',
                'ExampleNamespace/Gitty/Remote/Adapter/Invalid.php',
                'ExampleNamespace\\Gitty\\Remote\\Adapter',
                $remote_config,
                'Gitty\Remote\Exception'
            ),
            array(
                'Gitty\\Repositories',
                'ExampleNamespace/Gitty/Repositories/Adapter/Invalid.php',
                'ExampleNamespace\\Gitty\\Repositories\\Adapter',
                $repo_config,
                'Gitty\Repositories\Exception'
            )
        );
    }
}
