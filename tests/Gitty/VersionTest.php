<?php
namespace Gitty\Tests\Gitty;

use \Gitty as Gitty;

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/TestCase.php';

require_once \dirname(__FILE__).'/../../library/Gitty/Version.php';

/**
 * @outputBuffering disabled
 */
class VersionTest extends \PHPUnit_Framework_TestCase
{
    public function testVersionCompare()
    {
        $pieces = explode('.', Gitty\Version::VERSION);
        $this->assertEquals(Gitty\Version::compareVersion(\sprintf('%d.%d', $pieces[0]+1, $pieces[1])), 1);
        $this->assertEquals(Gitty\Version::compareVersion(\sprintf('%d.%d', $pieces[0]-1, $pieces[1])), -1);
        $this->assertEquals(Gitty\Version::compareVersion(Gitty\Version::VERSION), 0);

        $this->assertEquals(Gitty\Version::compareVersion(\sprintf('%d.%d', $pieces[0], $pieces[1]+1)), 1);
        $this->assertEquals(Gitty\Version::compareVersion(\sprintf('%d.%d', $pieces[0], $pieces[1]-1)), -1);
    }
}
