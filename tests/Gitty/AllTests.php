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

\PHPUnit_Util_Filter::addFileToFilter(__FILE__);

class AllTests
{
    public static function suite()
    {
        $ns = __NAMESPACE__;
        $suite = new \PHPUnit_Framework_TestSuite('Gitty Classes');
        $suite->addTestSuite("$ns\\LoaderTest");
        $suite->addTestSuite("$ns\\ConfigTest");
        return $suite;
    }
}
