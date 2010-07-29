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
require_once \dirname(__FILE__).'/Gitty/AllTests.php';

\PHPUnit_Util_Filter::addFileToFilter(__FILE__);
\PHPUnit_Util_Filter::addDirectoryToFilter(\dirname(__FILE__));
\date_default_timezone_set('Europe/Berlin');

class AllTests
{
    protected static function rrmdir($dir) {
        if (\is_dir($dir)) {
            $objects = \scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (\filetype($dir."/".$object) == "dir") {
                        self::rrmdir($dir."/".$object);
                    } else {
                        \unlink($dir."/".$object);
                    }
                }
            }
            \reset($objects);
            \rmdir($dir);
        }
    }

    public static function suite()
    {
        // unzip git meta data for our example repository
        $zip = new \ZipArchive;
        if (true === $zip->open(\dirname(__FILE__).'/data/example-git-repo.zip')) {
            $destination = \dirname(__FILE__).'/data/example';
            self::rrmdir($destination);
            \mkdir($destination);

            $zip->extractTo($destination);
            $zip->close();
        } else {
            throw new \Exception('can\'t open example meta data zip file');
        }

        $suite = new \PHPUnit_Framework_TestSuite('Gitty');
        $suite->addTestSuite(__NAMESPACE__.'\\Gitty\\AllTests');
        return $suite;
    }
}
