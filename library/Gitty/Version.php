<?php
final class Gitty_Version
{
    const VERSION = '0.1 PREVIEW';

    public static function compareVersion($version)
    {
        return version_compare($version, self::VERSION);
    }
}
