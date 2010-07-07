<?php
/**
 * Gitty - web deployment tool
 * Copyright (C) 2009 Fabian Grutschus
 *
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

/**
 * @namespace Gitty\Deploy\Adapter
 */
namespace Gitty\Deploy\Adapter;

class Ftp extends AdapterAbstract
{
    const METHOD = 'FTP';

    protected $_options = array('ftp' => array('overwrite' => true));

    protected function _setUrl()
    {
        $deploymentConfig = $this->_deploymentConfig;
        $this->_url = \sprintf('ftp://%s:%s@%s%s/',
                               $deploymentConfig['username'],
                               $deploymentConfig['password'],
                               $deploymentConfig['hostname'],
                               $deploymentConfig['path']);
    }

    protected function _deploy()
    {
        $files = $this->_files;

        $this->setMessage(Ftp\Messages::start($this->install, self::METHOD));

        $stat = \Gitty\Git\Command::exec(\Gitty\Git\Command::SHORT_DIFF($this->_remoteRevistionId, $this->_newestRevisitionId),
                                        $this->_projectConfig['repository'],
                                        $this->_config);

        $this->setMessage(Ftp\Messages::stat($stat[0]));

        $this->_createTemp();

        $add = $this->_files['added'];
        if (\count($add)) {
            $this->setMessage(Ftp\Messages::startAdd());

            foreach ($add as $file) {
                $this->setMessage(Ftp\Messages::add($file));

                $sourceFile = $this->_tempDir . $file;
                $destinationFile = $this->_url . $file;

                if (!\is_dir($this->_url . \dirname($file))) {
                    \mkdir($this->_url . \dirname($file), null, true, $this->_stream);
                }

                if (\version_compare(PHP_VERSION, '5.3.0') === 1) {
                    \copy($sourceFile, $destinationFile, $this->_stream);
                } else {
                    \file_put_contents($destinationFile, \file_get_contents($sourceFile), 0, $this->_stream);
                }
            }
        }

        $mod = $this->_files['modified'];
        if (\count($mod)) {
            $this->setMessage(Ftp\Messages::startModify());

            foreach ($mod as $file) {
                $this->setMessage(Ftp\Messages::modify($file));

                $sourceFile = $this->_tempDir . $file;
                $destinationFile = $this->_url . $file;

                if (!\is_dir($this->_url . \dirname($file))) {
                    \mkdir($this->_url . \dirname($file), null, true, $this->_stream);
                }

                if (\version_compare(PHP_VERSION, '5.3.0') === 1) {
                    \copy($sourceFile, $destinationFile, $this->_stream);
                } else {
                    \file_put_contents($destinationFile, \file_get_contents($sourceFile), 0, $this->_stream);
                }
            }
        }

        $this->setMessage(Ftp\Messages::revFile($this->_config->global['gitty']['revistionFile']));
        $this->_writeRevFile();

        $this->setMessage(Ftp\Messages::end($this->install, self::METHOD));

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