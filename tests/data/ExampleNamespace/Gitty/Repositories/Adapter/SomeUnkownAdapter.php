<?php
namespace ExampleNamespace\Gitty\Repositories\Adapter;

use \Gitty as Gitty;

class Foobar extends Gitty\Repositories\AdapterAbstract
{
    public function getOwner(){}
    public function setOwner($owner){}
    public function getLastChange(){}
    public function setLastChange(\DateTime $datetime){}
    public function getBranches(){}
    public function setBranches($branches){}
    public function getUpdateFiles($uid){}
    public function getInstallFiles(){}
    public function getFile($file){}
}
