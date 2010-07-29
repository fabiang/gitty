<?php
/**
 * @namespace Gitty\Tests\Gitty
 */
namespace Gitty\Tests\Gitty;

/**
 * required files
 */
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/Util/Filter.php';

require_once \dirname(__FILE__).'/ConfigTest.php';
require_once \dirname(__FILE__).'/LoaderTest.php';
require_once \dirname(__FILE__).'/VersionTest.php';
require_once \dirname(__FILE__).'/RepositoriesTest.php';
require_once \dirname(__FILE__).'/RemoteTest.php';
require_once \dirname(__FILE__).'/NamespaceLoadingTest.php';
require_once \dirname(__FILE__).'/Remote/AllTests.php';
require_once \dirname(__FILE__).'/Repositories/AllTests.php';
require_once \dirname(__FILE__).'/Observer/AllTests.php';
require_once \dirname(__FILE__).'/DeploymentTest.php';

\PHPUnit_Util_Filter::addFileToFilter(__FILE__);

class AllTests
{
    public static function suite()
    {
        $ns = __NAMESPACE__;
        $suite = new \PHPUnit_Framework_TestSuite('Gitty Classes');
        $suite->addTestSuite("$ns\\LoaderTest");
        $suite->addTestSuite("$ns\\ConfigTest");
        $suite->addTestSuite("$ns\\VersionTest");
        //$suite->addTestSuite("$ns\\RemoteTest");
        //$suite->addTestSuite("$ns\\Remote\\AllTests");
        $suite->addTestSuite("$ns\\RepositoriesTest");
        $suite->addTestSuite("$ns\\NamespaceLoadingTest");
        //$suite->addTestSuite("$ns\\Repositories\\AllTests");
        //$suite->addTestSuite("$ns\\Observer\\AllTests");
        //$suite->addTestSuite("$ns\\DeploymentTest");
        return $suite;
    }
}
