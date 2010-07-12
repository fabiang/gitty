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
 * along with Gitty.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @namespace Gitty\Repository\Adapter
 */
namespace Gitty\Repositories\Adapter;

/**
 * git repositories
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Git extends \Gitty\Repositories\AdapterAbstract
{
    protected $_defaultGitDir = '.git';

    protected $_descriptionFile = 'description';

    protected $_binLocation = '/usr/bin/git';

    protected $_gitDir = null;

    protected function _discoverGitDir()
    {
        if ($this->_gitDir === null) {
            if (\is_dir($this->_path . '/' . $this->_defaultGitDir)) {
                $this->_gitDir = realpath($this->_path . '/' . $this->_defaultGitDir);
            } else {
                $this->_gitDir = $this->_path;
            }
        }

        return $this->_gitDir;
    }

    protected function _execCommand($command)
    {
        $out = array();
        $command_complete = \sprintf('GIT_DIR=%s %s %s', $this->_discoverGitDir(), $this->_binLocation, $command);
        $dummy = \exec($command_complete, $out);
        return $out;
    }

    public function getDefaultGitDir()
    {
        return $this->_defaultGitDir;
    }
    public function setDefaultGitDir($git_dir)
    {
        $this->_defaultGitDir = $git_dir;
    }

    public function getDescriptionFile()
    {
        return $this->_descriptionFile;
    }
    public function setDescriptionFile($decription_file)
    {
        $this->_descriptionFile = $decription_file;
    }

    public function getBinLocation()
    {
        return $this->_binLocation;
    }
    public function setBinLocation($bin_location)
    {
        $this->_binLocation = $bin_location;
    }

    public function getOwner()
    {
        if ($this->_owner === null) {
            $ownerString = $this->_execCommand('rev-list --header --max-count=1 HEAD');

            foreach ($ownerString as $output) {
                if (\substr($output, 0, 9) == 'committer') {
                    $results = array();
                    \preg_match('/^ (.+) \d+ \+\d{4}$/', \substr($output, 9), $results);

                    $owner = \trim($results[1]);
                }
            }

            $this->setOwner($owner);
        }

        return $this->_owner;
    }
    public function setOwner($owner)
    {
        $this->_owner = $owner;
    }

    public function getLastChange()
    {
        if ($this->_lastChange === null) {

            $dateString = $this->_execCommand('rev-list --header --max-count=1 HEAD');

            foreach ($dateString as $output) {
                if (\substr($output, 0, 9) == 'committer') {
                    $results = array();
                    \preg_match('/^ .+ (\d+) \+\d{4}$/', \substr($output, 9), $results);

                    $date = \trim($results[1]);
                }
            }

            $dateTime = new \DateTime();
            $dateTime->setTimestamp($date);
            $this->setLastChange($dateTime);

        }

        return $this->_lastChange;
    }
    public function setLastChange(\DateTime $datetime)
    {
        $this->_lastChange = $datetime;
    }

    public function getBranches()
    {
        if ($this->showBranches() === true) {
            $this->_branches = array(array('name' => 'master', 'default' => 0));
            return $this->_branches;
        }

        if (\count($this->_branches) === 0) {
            $branchesString = $this->_execCommand('branch');

            $branches = array();
            foreach($branchesString as $branch) {
                if (\substr($branch, 0 ,1) == '*') {
                    $branches[] = array(
                        'name' => \trim(\substr($branch, 1)),
                        'default' => 1
                    );
                } else {
                    $branches[] = array(
                        'name' => \trim($branch),
                        'default' => 0
                    );
                }
            }

            $this->setBranches($branches);
        }

        return $this->_branches;
    }

    public function setBranches($branches)
    {
        $this->_branches = $branches;
    }

    public function getUpdateFiles($uid)
    {

    }

    public function getInstallFiles()
    {
    }
}
