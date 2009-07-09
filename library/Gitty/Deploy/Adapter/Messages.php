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

    public static function stat($stat)
    {
        return $stat;
    }

    public static function startAdd()
    {
        return 'add files...';
    }

    public static function startModify()
    {
        return 'modified files...';
    }

    public static function startDelete()
    {
        return 'deleting files...';
    }

    public static function startCopy()
    {
        return 'copy files...';
    }

    public static function startRename()
    {
        return 'rename files...';
    }

    public static function revFile($file)
    {
        return 'write revisition file "'.$file.'"...';
    }
}