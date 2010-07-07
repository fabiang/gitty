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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Gitty.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @namespace Gitty
 */
namespace Gitty;

class Deploy
{
    static $defaultAdapter = 'Deploy\\Adapter\\Ftp';

    protected $_adapterType = 'ftp';
    protected $_adapter;
    protected $_config;
    protected $_projectId = 0;
    protected $_projectConfigs;
    protected $_projectConfig;
    protected $_deploymentId = 0;
    protected $_deploymentConfig;
    protected $_branch;
    protected $_oldBranch = 'master';

    public function __construct($config)
    {
        $this->_config = $config;
        $this->_projectConfigs = array_values($config->projects);
    }

    public function setProjectId($id)
    {
        $this->_projectId = $id;
        $this->updateConfig();
    }

    public function updateConfig()
    {
        $this->_projectConfig = $this->_projectConfigs[$this->_projectId];
    }

    public function setBranch($name)
    {
        $this->_branch = $name;

        $branchesString = Git\Command::exec(Git\Command::BRANCHES(), $this->_projectConfig['repository'], $this->_config);

        $currentBranch = 'master';
        foreach($branchesString as $branch) {
            if (substr($branch, 0 ,1) == '*') {
                $currentBranch = trim(substr($branch, 1));
                break;
            }
        }

        $this->_oldBranch = $currentBranch;

        $this->_adapter->branch = $name;

        Git\Command::exec(Git\Command::BRANCH($name), $this->_projectConfig['repository'], $this->_config);
    }

    public function setDeploymentId($id)
    {
        $this->_deploymentId = $id;
        $this->setDeploymentConfig();
    }

    public function setDeploymentConfig($config = null)
    {
        if ($config) {
            $this->_deploymentConfig = $config;
        } else {
            $deployment = $this->_projectConfig['deployment'];
            $adapter = $deployment['adapter'][$this->_deploymentId];
            $config = array('adapter' => $adapter);
            $this->_adapterType = $adapter;

            foreach($deployment as $name => $value) {
                $config[$name] = $value[$this->_deploymentId];
            }

            $this->_deploymentConfig = $config;
        }
    }

    public function start()
    {
        $adapterName = 'Gitty\\Deploy\\Adapter\\' . ucfirst(strtolower($this->_adapterType));

        try {
            $adapter = Loader::loadClass($adapterName);
            $adapter = new $adapterName;
        } catch(Exception $e) {
            require_once 'Gitty/Deploy/Exception.php';
            throw new Deploy\Exception("Can't create instance of adapter '$adapterName'. Does adapter with name '$this->_adapterType' exist?");
        }

        $this->_adapter = $adapter;
        $this->_adapter->start($this->_config, $this->_deploymentConfig, $this->_projectConfig);
    }

    public function open()
    {
        $this->_adapter->open();
    }

    public function close()
    {
        $this->_adapter->close();
        Git\Command::exec(Git\Command::BRANCH($this->_oldBranch), $this->_projectConfig['repository'], $this->_config);
    }

    public function hasFinished()
    {
        return $this->_adapter->hasFinished();
    }

    public function newMessage()
    {
        return $this->_adapter->newMessage();
    }

    public function getLastStatusName()
    {
        return $this->_adapter->getLastStatusName();
    }

    public function getLastStatus()
    {
        return $this->_adapter->getLastStatus();
    }

    public function setCallback($callback)
    {
        $this->_adapter->setCallback($callback);
    }

    public function getCallback()
    {
        return $this->_adapter->getCallback();
    }

    public function count()
    {
        return $this->_adapter->count();
    }

    public function install($install = true)
    {
        $this->_adapter->install = (bool)$install;
    }
}