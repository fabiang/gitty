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
     * observers
     */
    protected $_observers = array();

    /**
     * call a method of all observers
     *
     * @param String $function the function name
     */
    protected function _callObservers($function)
    {
        $params = array();
        // if there are more parameters, turn them into array
        if (\func_num_args() > 1) {
            $params = \func_get_args();
            // remove the function name, it's already in $function
            unset($params[0]);
        }

        foreach($this->_observers as $observer) {
            if (\method_exists($observer, $function)) {
                \call_user_func_array(array($observer, $function), $params);
            }
        }
    }

    /**
     * constructor
     */
    public function __construct(Config $config)
    {
        $this->_repositories = new Repositories($config);
    }

    public function __destruct()
    {
        $this->end();
    }

    public function registerObserver(Observer\ObserverInterface $observer)
    {
        $this->_observers[] = $observer;
    }

    public function unregisterObserver(Observer\ObserverInterface $observer)
    {
        $index = \array_search($observer, $this->_observers, true);
        unset($this->_observers[$index]);
        return !!$index;
    }

    public function getProjectId()
    {
        return $this->_project_id;
    }

    public function setProjectId($id)
    {
        $this->_project_id = $id;
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

    public function start()
    {
        $this->_callObservers('onStart');
    }

    public function end()
    {
    }
}
