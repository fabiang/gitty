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
*
 * PHP Version 5.3
 *
 * @category Gitty
 * @package  Deployment
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Deployment
 */

/**
 * @namespace Gitty
 */
namespace Gitty;

/**
 * deployment class
 *
 * @category Gitty
 * @package  Deployment
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Deployment
 */
class Deployment
{
    /**
     * project id
     */
    protected $project_id = 0;

    /**
     * branch
     */
    protected $branch = null;

    /**
     * remote id
     */
    protected $remote_id = 0;

    /**
     * repositories object
     */
    protected $repositories = null;

    /**
     * current deployed repository
     */
    protected $deploy_repository = null;

    /**
     * current remote to deploy
     */
    protected $deploy_remote = null;

    /**
     * observers
     */
    protected $observers = array();

    /**
     * the observers when cleaned
     */
    protected $clean = false;

    /**
     * deployment should install instead of update
     */
    public $install = false;

    /**
     * reference to the config object
     */
    public $config = null;

    /**
     * start time of deployment
     */
    public $start = null;

    /**
     * call a method of all observers
     *
     * @param String $function the function name
     *
     * @return Null
     */
    protected function callObservers($function)
    {
        $params = array($this);
        // if there are more parameters, turn them into array
        if (\func_num_args() > 1) {
            $params = \array_merge($params, \array_slice(\func_get_args(), 1));
        }

        foreach ($this->observers as $observer) {
            if (\method_exists($observer, $function)) {
                \call_user_func_array(array($observer, $function), $params);
            }
        }
    }

    /**
     * get files for installation/update
     *
     * @return Array array contains the modified/added/deleted/renamed/copied files
     */
    protected function getFiles()
    {
        $repo = $this->getCurrentRepository();
        if (true === $this->install) {
            return $repo->getInstallFiles();
        }

        $remote = $this->getCurrentRemote();
        return $repo->getUpdateFiles($remote->getServerRevisitionId());
    }

    /**
     * write a file with the revisition file to the server
     *
     * @return Null
     */
    protected function writeRevistionFile()
    {
        $repo = $this->getCurrentRemote()->putServerRevisitionId(
            $this->getCurrentRepository()->getNewestRevisitionId()
        );
    }

    /**
     * constructor
     *
     * @param Gitty\Config $config  configuration object
     * @param Boolean      $install install should be performed
     */
    public function __construct(Config $config, $install = false)
    {
        $this->repositories = new Repositories($config);
        $this->config = $config;
        $this->install = $install;
    }

    /**
     * destructor, calling the end function of the object
     */
    public function __destruct()
    {
        $this->end();
    }

    /**
     * gets the current repository
     *
     * @param Boolean $reset reset repository look up
     *
     * @return Gitty\Repositories\AdapterAbstract the current repository
     */
    public function getCurrentRepository($reset = false)
    {
        if (null === $this->deploy_repository || true === $reset) {
            // get repostories from repository object
            $repos = $this->repositories->getRepositories();
            if (!isset($repos[$this->project_id])) {
                include_once \dirname(__FILE__).'/Exception.php';
                throw new Exception(
                    'there is no repository registered with the id ' .
                    $this->project_id .
                    ' (only '.\count($repos).' repositories registered)'
                );
            }

            $this->deploy_repository = $repos[$this->project_id];
        }

        return $this->deploy_repository;
    }

    /**
     * get current remote object
     *
     * @param Boolean $reset reset remite look up
     *
     * @return Gitty\Remote\AdapterAbstract the current remote
     */
    public function getCurrentRemote($reset = false)
    {
        if (null === $this->deploy_remote || true === $reset) {
            // get remotes from repositories object
            $remote = $this->getCurrentRepository()->getRemotes();
            if (!isset($remote[$this->remote_id])) {
                include_once \dirname(__FILE__).'/Exception.php';
                throw new Exception(
                    'there is no remote registered with the id ' .
                    $this->remote_id .
                    ' (only '.\count($remote).' repositories registered)'
                );
            }

            $this->deploy_remote = $remote[$this->remote_id];
        }

        return $this->deploy_remote;
    }

    /**
     * get registered observers
     *
     * @return Array observers
     */
    public function getObservers()
    {
        return $this->observers;
    }

    /**
     * register an observer
     *
     * @param Gitty\Observer\ObserverInterface $observer observer
     *
     * @return Null
     */
    public function registerObserver(Observer\ObserverInterface $observer)
    {
        $this->observers[] = $observer;
    }

    /**
     * unregister an observer
     *
     * @param Gitty\Observer\ObserverInterface $observer observer
     *
     * @return Boolean true when observer was removed, otherwise false
     */
    public function unregisterObserver(Observer\ObserverInterface $observer)
    {
        $index = \array_search($observer, $this->observers, true);
        if (false !== $index) {
            unset($this->observers[$index]);
            return true;
        }

        return false;
    }

    /**
     * get project id
     *
     * @return Integer project id
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * set porject id
     *
     * @param Integer $id project id
     *
     * @return Null
     */
    public function setProjectId($id)
    {
        $this->project_id = (int)$id;
        $this->getCurrentRepository();
    }

    /**
     * get branch
     *
     * @return String branch name
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * set branch
     *
     * @param String $branch branch name
     *
     * @return Null
     */
    public function setBranch($branch)
    {
        $this->branch = $branch;
    }

    /**
     * get remote id
     *
     * @return Integer remote id
     */
    public function getRemoteId()
    {
        return $this->remote_id;
    }

    /**
     * set remote id
     *
     * @param Integer $remote remote id
     *
     * @return Null
     */
    public function setRemoteId($remote)
    {
        $this->remote_id = (int)$remote;
        $this->getCurrentRemote();
    }

    /**
     * start the updating installation process
     * and call the obeservers while performing
     *
     * @return Null
     */
    public function start()
    {
        $this->callObservers('onStart');

        $remote = $this->getCurrentRemote();
        $repo = $this->getCurrentRepository();

        //init remote
        $remote->init();

        $datetime = new \DateTime();
        $datetime->format($this->config->global->gitty->dateFormat);
        $this->start = $datetime;

        $newest = $this->getCurrentRepository()->getNewestRevisitionId();
        if ($newest == $remote->getServerRevisitionId()
            && false === $this->install
        ) {
            $this->callObservers('onUpToDate');
            return;
        }

        $files = $this->getFiles();

        // call stat on the observers
        $this->callObservers('onStat', $files);

        //first the added files
        $added = $files['added'];
        if (\count($added) > 0) {
            $this->callObservers('onAddStart');

            foreach ($added as $file) {
                $this->callObservers('onAdd', $file);
                $remote->put($repo->getFile($file), $file);
            }

            $this->callObservers('onAddEnd');
        }

        // the modified files are copied too
        $modified = $files['modified'];
        if (\count($modified) > 0) {
            $this->callObservers('onModifiedStart');

            foreach ($modified as $file) {
                $this->callObservers('onModified', $file);
                $remote->put($repo->getFile($file), $file);
            }

            $this->callObservers('onModifiedEnd');
        }

        // copied files
        $copied = $files['copied'];
        if (\count($copied) > 0) {
            $this->callObservers('onCopiedStart');

            foreach ($copied as $file) {
                $source = \array_keys($file);
                $destination = \array_values($file);

                $this->callObservers('onCopied', $source[0]);
                $remote->copy($source[0], $destination[0]);
            }

            $this->callObservers('onCopiedEnd');
        }

        // renamed files
        $renamed = $files['renamed'];
        if (\count($renamed) > 0) {
            $this->callObservers('onRenamedStart');

            foreach ($renamed as $file) {
                $source = \array_keys($file);
                $destination = \array_values($file);

                $this->callObservers('onRenamed', $source[0], $destination[0]);
                $remote->rename($source[0], $destination[0]);
            }

            $this->callObservers('onRenamedEnd');
        }

        // deleted files
        $deleted = $files['deleted'];
        if (\count($deleted) > 0) {
            $this->callObservers('onDeletedStart');

            foreach ($deleted as $file) {
                $this->callObservers('onDeleted', $file);
                $remote->unlink($file);
            }

            $this->callObservers('onDeletedEnd');
        }

        // write revisition id to a file
        $this->writeRevistionFile();

        $remote->cleanUp();
    }

    /**
     * call onEnd event on the observer
     *
     * @return Null
     */
    public function end()
    {
        if (false === $this->clean) {
            $this->callObservers('onEnd');
            $this->clean = true;
        }
    }
}
