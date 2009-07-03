<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

set_include_path(
    realpath(dirname(__FILE__) . '/library') .
    PATH_SEPARATOR . get_include_path()
);

require_once 'library/Gitty/Loader.php';
Gitty_Loader::registerAutoload();