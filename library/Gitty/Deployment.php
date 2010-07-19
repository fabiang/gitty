<?php
/**
 * Gitty - web deployment tool
 * Copyright (C) 2010 Fabian Grutschus
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
 * along with Gitty. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @namespace Gitty
 */
namespace Gitty;

/**
 * deployment class
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Deployment
{
    /**
     * $_project_id
     */
    protected $_project_id = 0;

    /**
     * branch
     */
    protected $_branch = null;

    /**
     * remote id
     */
    protected $_remote = 0;

    /**
     * repositories object
     */
    protected $_repositories = null;

    /**
     * current deployed repository
     */
    protected $_deploy_repository = null;

    /**
     * current remote to deploy
     */
    protected $_deploy_remote = null;

    /**
     * observers
     */
    protected $_observers = array();

    /**
     * deployment should install instead of update
     */
    public $install = false;

    /**
     * reference to the config object
     */
    public $config = null;

    /**
     * call a method of all observers
     *
     * @param String $function the function name
     */
    protected function _callObservers($function)
    {
        $params = array($this);
        // if there are more parameters, turn them into array
        if (\func_num_args() > 1) {
            $params = array_merge($params, \func_get_args());
            // remove the function name, it's already in $function
            unset($params[0]);
        }

        foreach($this->_observers as $observer) {
            if (\method_exists($observer, $function)) {
                \call_user_func_array(array($observer, $function), $params);
            }
        }
    }

    protected function _getCurrentRepository($reset = false)
    {
        if ($this->_deploy_repository === null || $reset === true) {
            // get repostories from repository object
            $repos = $this->_repositories->getRepositories();
            $this->_deploy_repository = $repos[$this->_project_id];
        }

        return $this->_deploy_repository;
    }

    protected function _getCurrentRemote($reset = false)
    {
        if ($this->_deploy_remote === null || $reset === true) {
            // get remotes from repositories object
            $remote = $this->_getCurrentRepository()->getRemotes();
            $this->_deploy_remote = $remote[$this->_project_id];
        }

        return $this->_deploy_remote;
    }

    protected function _getFiles()
    {
        $repo = $this->_getCurrentRepository();
        if ($this->install === true) {
            return $repo->getInstallFiles();
        }

        $remote = $this->_getCurrentRemote();
        return $repo->getUpdateFiles($remote->getServerRevisitionId());
    }

    protected function _writeRevistionFile()
    {
        $repo = $this->_getCurrentRemote()->putServerRevisitionId(
            $this->_getCurrentRepository()->getNewestRevisitionId()
        );
    }

    /**
     * constructor
     */
    public function __construct(Config $config, $install = false)
    {
        $this->_repositories = new Repositories($config);
        $this->config = $config;
        $this->install = $install;
    }

    public function __destruct()
    {
        $this->end();
    }

    public function getObersers()
    {
        return $this->_observers;
    }

    public function registerObserver(Observer\ObserverInterface $observer)
    {
        $this->_observers[] = $observer;
    }

    public function unregisterObserver(Observer\ObserverInterface $observer)
    {
        $index = \array_search($observer, $this->_observers, true);
        if (isset($this->_observers[$index])) {
            unset($this->_observers[$index]);
        }
        return !!$index;
    }

    public function getProjectId()
    {
        return $this->_project_id;
    }

    public function setProjectId($id)
    {
        $this->_project_id = $id;
        $this->_getCurrentRepository();
    }

    public function getBranch()
    {
        return $this->_branch;
    }

    public function setBranch($branch)
    {
        $this->_branch = $branch;
    }

    public function getRemoteId()
    {
        return $this->_remote;
    }

    public function setRemoteId($remote)
    {
        $this->_remote = $remote;
    }

    /**
     *
     */
    public function start()
    {
        $this->_callObservers('onStart');

        $remote = $this->_getCurrentRemote();
        $repo = $this->_getCurrentRepository();

        $files = $this->_getFiles();

        //first the added files
        $added = $files['added'];
        if (\count($added) > 0) {
            $this->_callObservers('onAddStart');

            foreach($added as $file) {
                $fullpath = $repo->getPath() . '/' . $file;
                $this->_callObservers('onAdd', $file);
                $remote->copy($fullpath, $file);
            }

            $this->_callObservers('onAddEnd');
        }

        $modified = $files['modified'];
        if (\count($modified) > 0) {
            $this->_callObservers('onModifiedStart');

            foreach($modified as $file) {
                $fullpath = $repo->getPath() . '/' . $file;
                $this->_callObservers('onModified', $file);
                $remote->copy($fullpath, $file);
            }

            $this->_callObservers('onModifiedEnd');
        }

        $copied = $files['copied'];
        if (\count($copied) > 0) {
            $this->_callObservers('onCopiedStart');

            foreach($copied as $file) {
                $fullpath = $repo->getPath() . '/' . $file;
                $this->_callObservers('onCopied', $file);
                $remote->copy($fullpath, $file);
            }

            $this->_callObservers('onCopiedEnd');
        }

        $renamed = $files['renamed'];
        if (\count($renamed) > 0) {
            $this->_callObservers('onRenamedStart');

            foreach($renamed as $file) {
                $fullpath = $repo->getPath() . '/' . $file;
                $this->_callObservers('onRenamed', $file);
                $remote->rename($fullpath, $file);
            }

            $this->_callObservers('onRenamedEnd');
        }

        $deleted = $files['deleted'];
        if (\count($deleted) > 0) {
            $this->_callObservers('onDeletedStart');

            foreach($deleted as $file) {
                $fullpath = $repo->getPath() . '/' . $file;
                $this->_callObservers('onDeleted', $file);
                $remote->unlink($fullpath);
            }

            $this->_callObservers('onDeletedEnd');
        }

        // write revisition id to a file
        $this->_writeRevistionFile();
    }

    public function end()
    {
        $this->_callObservers('onEnd');
    }
}
