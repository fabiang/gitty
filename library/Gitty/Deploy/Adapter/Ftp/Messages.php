<?php
class Gitty_Deploy_Adapter_Ftp_Messages extends Gitty_Deploy_Adapter_Messages
{
    public static function start($install, $method)
    {
        return 'Starting ' . ($install ? 'Install' : 'Update') . ' with Method "' . $method .'"';
    }

    public static function modify($file)
    {
        return 'Replace "'. $file .'"';
    }

    public static function add($file)
    {
        return 'Uploading "'. $file .'"';
    }

    public static function delete($file)
    {
        return 'Delete "'. $file .'"';
    }

    public static function copy($file)
    {
        return 'Copy "'. $file .'"';
    }

    public static function rename($file)
    {
        return 'Rename "'. $file .'"';
    }

    public static function end($install, $method)
    {
        return 'Finished ' . ($install ? 'Install' : 'Update') . ' with Method "' . $method .'"';
    }
}