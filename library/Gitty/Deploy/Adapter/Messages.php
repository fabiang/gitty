<?php
abstract class Gitty_Deploy_Adapter_Messages
{
    abstract public static function start($install, $method);
    abstract public static function modify($file);
    abstract public static function add($file);
    abstract public static function delete($file);
    abstract public static function copy($file);
    abstract public static function rename($file);
    abstract public static function end($install, $method);

    final public static function stat($stat)
    {
        return $stat;
    }

    final public static function startAdd()
    {
        return 'add files...';
    }

    final public static function startModify()
    {
        return 'modified files...';
    }

    final public static function startDelete()
    {
        return 'deleting files...';
    }

    final public static function startCopy()
    {
        return 'copy files...';
    }

    final public static function startRename()
    {
        return 'rename files...';
    }

    final public static function revFile($file)
    {
        return 'write revisition file "'.$file.'"...';
    }

    final public static function upToDate()
    {
        return 'everything up-to-date... nothing to do';
    }
}