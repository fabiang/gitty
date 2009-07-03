<?php
class Gitty_Debug
{
    public static function dump($data)
    {
        print '<pre>';
        var_dump($data);
        print '</pre>';
    }
}