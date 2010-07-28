<?php
namespace ExampleNamespace\Gitty\Remote\Adapter;

use \Gitty as Gitty;

class Foobar extends Gitty\Remote\AdapterAbstract
{
    public function getServerRevisitionId(){}
    public function putServerRevisitionId($uid){}
    public function put($file, $destination){}
    public function rename($source, $destination){}
    public function unlink($file, $remove_empty_directories = true){}
    public function copy($source, $destination){}
    public function init(){}
    public function cleanUp(){}
    public function __toString(){}
}
