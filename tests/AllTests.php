<?php
/**
 * @namespace Gitty\Tests
 */
namespace Gitty\Tests;

/**
 * required files
 */
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/Util/Filter.php';
require_once dirname(__FILE__).'/Gitty/AllTests.php';

\PHPUnit_Util_Filter::addFileToFilter(__FILE__);

class AllTests
{
    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite('Gitty');
        $suite->addTestSuite('\\Gitty\\Tests\\Gitty\\AllTests');
        return $suite;
    }
}
