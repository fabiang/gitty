<?php
class Gitty_Deploy_Adapter_Ftp extends Gitty_Deploy_Adapter_Abstract
{
    const METHOD = 'FTP';

    protected $_options = array('ftp' => array('overwrite' => true));

    protected function _setUrl()
    {
        $deploymentConfig = $this->_deploymentConfig;
        $this->_url = sprintf('ftp://%s:%s@%s%s/',
                               $deploymentConfig['username'],
                               $deploymentConfig['password'],
                               $deploymentConfig['hostname'],
                               $deploymentConfig['path']);
    }

    protected function _deploy()
    {
        $files = $this->_files;

        $this->setMessage(Gitty_Deploy_Adapter_Ftp_Messages::start($this->install, self::METHOD));

        $stat = Gitty_Git_Command::exec(Gitty_Git_Command::SHORT_DIFF($this->_remoteRevistionId, $this->_newestRevisitionId),
                                        $this->_projectConfig['repository'],
                                        $this->_config);
        $this->setMessage(Gitty_Deploy_Adapter_Ftp_Messages::stat($stat[0]));

        $add = $this->_files['added'];
        if (count($add)) {
            $this->setMessage(Gitty_Deploy_Adapter_Ftp_Messages::startAdd());

            foreach ($add as $file) {
                $this->setMessage(Gitty_Deploy_Adapter_Ftp_Messages::add($file));
            }
        }

        $mod = $this->_files['modified'];
        if (count($mod)) {
            $this->setMessage(Gitty_Deploy_Adapter_Ftp_Messages::startModify());

            foreach ($mod as $file) {
                $this->setMessage(Gitty_Deploy_Adapter_Ftp_Messages::modify($file));
            }
        }

        $this->setMessage(Gitty_Deploy_Adapter_Ftp_Messages::revFile($this->_config->global['gitty']['revistionFile']));
        $this->_writeRevFile();

        $this->setMessage(Gitty_Deploy_Adapter_Ftp_Messages::end($this->install, self::METHOD));

        $this->_finished = true;
    }

    public function getLastStatusName()
    {
    }

    public function getLastStatus()
    {
    }
}