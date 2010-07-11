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

require_once dirname(__FILE__).'/ConfigTest.php';

\PHPUnit_Util_Filter::addFileToFilter(__FILE__);

class AllTests
{

    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite('Gitty Classes');
        $suite->addTestSuite('\\Gitty\\Tests\\Gitty\\ConfigTest');
        return $suite;
    }
}