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
 * storthands
 */
use \Gitty as G;

/**
 * git repository adapter
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 * @todo make defaultGitDir a static and his access functions
 */
class Git extends G\Repositories\AdapterAbstract
{
    /**
     * default git meta directory, by default it's named .git
     */
    protected $_defaultGitDir = '.git';

    /**
     * name of the description file
     * git's default is "description"
     */
    protected $_descriptionFile = 'description';

    /**
     * default path to the git excutable
     * /usr/bin/git should be a good location
     */
    protected $_binLocation = '/usr/bin/git';

    /**
     * the auto discovered git dir for the repository
     */
    protected $_gitDir = null;

    /**
     * auto discover the git directory
     *
     * @return String git directory
     */
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

    /**
     * exec a git command
     */
    protected function _execCommand($command)
    {
        $out = array();
        $command_complete = \sprintf('GIT_DIR=%s %s %s', $this->_discoverGitDir(), $this->_binLocation, $command);
        $dummy = \exec($command_complete, $out);
        return $out;
    }

    /**
     * get newest and oldest revisition id
     *
     * @param Boolean $reset reset the values from the class
     * @return Array newest and oldest as keys
     */
    protected function _getRevId($reset = false)
    {
        if (($this->_newestRevisitionId === null && $this->_oldestRevisitionId === null) || $reset === true) {
            $revs = $this->_execCommand('rev-list --all --full-history --topo-order');

            $this->_newestRevisitionId = $revs[0];
            $this->_oldestRevisitionId = \end($revs);
        }

        return array('newest' => $this->_newestRevisitionId, 'oldest' => $this->_oldestRevisitionId);
    }

    /**
     * parse files form git diff
     *
     * @param Array $diff the diff as array
     * @return Array an array containing the deleted, modified, copied, renamed, added files
     */
    protected function _parseFiles($diff)
    {
        foreach ($diff as $file) {
            $fileInfo = \preg_split('#\s+#', $file);

            switch ($fileInfo[0]) {
                case 'D':
                    \array_push($this->_deleted, $fileInfo[1]);
                    break;
                case 'M':
                    \array_push($this->_modified, $fileInfo[1]);
                    break;
                case 'C':
                    \array_push($this->_copied, $fileInfo[1]);
                    break;
                case 'R':
                    \array_push($this->_renamed, $fileInfo[1]);
                    break;
                case 'A':
                default:
                    \array_push($this->_added, $fileInfo[1]);
                    break;
            }
        }

        return array(
            'deleted'  => $this->_deleted,
            'modified' => $this->_modified,
            'copied'   => $this->_copied,
            'renamed'  => $this->_renamed,
            'added'    => $this->_added
        );
    }

    /**
     * constructor
     *
     * @param Array $options options for the adapter
     * @todo throw exception if git is not found
     */
    public function __construct($options)
    {
        parent::__construct($options);

        // get description from file
        if ($this->getDescription() === '') {
            $descFile = $this->_discoverGitDir() . '/' . $this->_descriptionFile;

            if (\is_file($descFile)) {

                if (!\is_readable($descFile)) {
                    require_once \dirname(__FILE__) . '/../Exception.php';
                    throw new G\Repositories\Exception("'$projectName' contains a description file, but it's not readable");
                }

                $this->setDescription(\trim(\strip_tags(\file_get_contents($descFile))));
            }
        }
    }

    /**
     * get the default git directory
     *
     * @return String git directory
     */
    public function getDefaultGitDir()
    {
        return $this->_defaultGitDir;
    }

    /**
     * set default git directory
     *
     * @param String $git_dir default git dir
     */
    public function setDefaultGitDir($git_dir)
    {
        $this->_defaultGitDir = $git_dir;
    }

    /**
     * get name of the description file
     *
     * @return String name of the description file
     */
    public function getDescriptionFile()
    {
        return $this->_descriptionFile;
    }

    /**
     * set name of the description file
     *
     * @param String $decription_file name of the description file
     */
    public function setDescriptionFile($decription_file)
    {
        $this->_descriptionFile = $decription_file;
    }

    /**
     * get location of the git excutable
     *
     * @return String path to git
     */
    public function getBinLocation()
    {
        return $this->_binLocation;
    }

    /**
     * set location of the git excutable
     *
     * @param String $bin_location path to git
     */
    public function setBinLocation($bin_location)
    {
        $this->_binLocation = $bin_location;
    }

    /**
     * get owner of the repository
     *
     * @return String owner
     */
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

    /**
     * set owner of the repository
     *
     * @param String $owner owner
     */
    public function setOwner($owner)
    {
        $this->_owner = $owner;
    }

    /**
     * get last change date of the repository
     *
     * @return \DateTime DateTime object with last change date
     */
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

    /**
     * set last change date of the repository
     *
     * @param \DateTime $datetime DateTime object with last change date
     */
    public function setLastChange(\DateTime $datetime)
    {
        $this->_lastChange = $datetime;
    }

    /**
     * get branches
     *
     * @return Array branches
     */
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

    /**
     * set branches
     *
     * @param Array $branches branches as array
     */
    public function setBranches($branches)
    {
        $this->_branches = $branches;
    }

    /**
     * get newest revisition id
     *
     * @return String newest id
     */
    public function getNewestRevisitionId()
    {
        list($newest, ) = array_values($this->_getRevId());
        return $newest;
    }

    /**
     * get updates file since a version id
     *
     * @param String $uuid version id
     * @return Array an array containing the deleted, modified, copied, renamed, added files
     */
    public function getUpdateFiles($uid)
    {
        list($newest, $oldest) = array_values($this->_getRevId());
        $files = $this->_execCommand('diff -M -C --name-status '. $uid . ' ' . $newest);
        return $this->_parseFiles($files);
    }

    /**
     * get alles files from the repository
     *
     * @return Array an array containing the deleted, modified, copied, renamed, added files
     */
    public function getInstallFiles()
    {
        list($newest, $oldest) = array_values($this->_getRevId());
        $files = $this->_execCommand('ls-files --full-name');

        return array(
            'deleted'  => array(),
            'modified' => array(),
            'copied'   => array(),
            'renamed'  => array(),
            'added'    => $files
        );
    }
}
