<?php
/**
 * This file is part of Gitty.
 *
 * Gitty is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Gitty is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Gitty.  If not, see <http://www.gnu.org/licenses/>.
 */
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

        $this->_createTemp();

        $add = $this->_files['added'];
        if (count($add)) {
            $this->setMessage(Gitty_Deploy_Adapter_Ftp_Messages::startAdd());

            foreach ($add as $file) {
                $this->setMessage(Gitty_Deploy_Adapter_Ftp_Messages::add($file));

                $sourceFile = $this->_tempDir . $file;
                $destinationFile = $this->_url . $file;

                if (!is_dir($this->_url . dirname($file))) {
                    mkdir($this->_url . dirname($file), null, true, $this->_stream);
                }

                if (version_compare(PHP_VERSION, '5.3.0') === 1) {
                    copy($sourceFile, $destinationFile, $this->_stream);
                } else {
                    file_put_contents($destinationFile, file_get_contents($sourceFile), 0, $this->_stream);
                }
            }
        }

        $mod = $this->_files['modified'];
        if (count($mod)) {
            $this->setMessage(Gitty_Deploy_Adapter_Ftp_Messages::startModify());

            foreach ($mod as $file) {
                $this->setMessage(Gitty_Deploy_Adapter_Ftp_Messages::modify($file));

                $sourceFile = $this->_tempDir . $file;
                $destinationFile = $this->_url . $file;

                if (!is_dir($this->_url . dirname($file))) {
                    mkdir($this->_url . dirname($file), null, true, $this->_stream);
                }

                if (version_compare(PHP_VERSION, '5.3.0') === 1) {
                    copy($sourceFile, $destinationFile, $this->_stream);
                } else {
                    file_put_contents($destinationFile, file_get_contents($sourceFile), 0, $this->_stream);
                }
            }
        }

        $this->setMessage(Gitty_Deploy_Adapter_Ftp_Messages::revFile($this->_config->global['gitty']['revistionFile']));
        $this->_writeRevFile();

        $this->setMessage(Gitty_Deploy_Adapter_Ftp_Messages::end($this->install, self::METHOD));

        $this->_deleteTemp();

        $this->_finished = true;
    }

    public function getLastStatusName()
    {
    }

    public function getLastStatus()
    {
    }
}