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
 *
 * PHP Version 5.3
 *
 * @category Gitty
 * @package  Git
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Repositories/Adapter/Git
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
 * @category Gitty
 * @package  Git
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Repositories/Adapter/Git
 * @todo make defaultGitDir a static and his access functions
 */
class Git extends G\Repositories\AdapterAbstract
{
    /**
     * default git meta directory, by default it's named .git
     */
    protected $defaultGitDir = '.git';

    /**
     * name of the description file
     * git's default is "description"
     */
    protected $descriptionFile = 'description';

    /**
     * default path to the git excutable
     * /usr/bin/git should be a good location
     */
    protected $binLocation = '/usr/bin/git';

    /**
     * the auto discovered git dir for the repository
     */
    protected $gitDir = null;

    /**
     * oldest revisition id
     */
    protected $oldestRevisitionId = null;

    /**
     * newest revisition id
     */
    protected $newestRevisitionId = null;

    /**
     * auto discover the git directory
     *
     * @return String git directory
     */
    protected function discoverGitDir()
    {
        if (null === $this->gitDir) {
            if (\is_dir($this->path . '/' . $this->defaultGitDir)) {
                $this->gitDir = realpath(
                    $this->path . '/' .
                    $this->defaultGitDir
                );
            } else {
                $this->gitDir = $this->path;
            }
        }

        return $this->gitDir;
    }

    /**
     * exec a git command
     *
     * @param String $command exec command
     *
     * @return Null
     */
    protected function execCommand($command)
    {
        $out = array();
        $command_complete = \sprintf(
            'GIT_DIR=%s %s %s',
            $this->discoverGitDir(),
            $this->binLocation,
            $command
        );
        $dummy = \exec($command_complete, $out);
        return $out;
    }

    /**
     * parse files form git diff
     *
     * @param Array $diff the diff as array
     *
     * @return Array an array containing files
     */
    protected function parseFiles($diff)
    {
        $this->deleted = array();
        $this->modified  = array();
        $this->copied = array();
        $this->renamed = array();
        $this->added = array();

        foreach ($diff as $file) {
            $fileInfo = \preg_split('#\s+#', $file);

            switch (\substr($fileInfo[0], 0, 1)) {
            case 'D':
                \array_push($this->deleted, $fileInfo[1]);
                break;
            case 'M':
                \array_push($this->modified, $fileInfo[1]);
                break;
            case 'C':
                \array_push($this->copied, array($fileInfo[1] => $fileInfo[2]));
                break;
            case 'R':
                \array_push($this->renamed, array($fileInfo[1] => $fileInfo[2]));
                break;
            case 'A':
            default:
                \array_push($this->added, $fileInfo[1]);
                break;
            }
        }

        return array(
            'deleted'  => $this->deleted,
            'modified' => $this->modified,
            'copied'   => $this->copied,
            'renamed'  => $this->renamed,
            'added'    => $this->added
        );
    }

    /**
     * constructor
     *
     * @param Array $options options for the adapter
     *
     * @todo throw exception if git is not found
     */
    public function __construct($options)
    {
        parent::__construct($options);

        // get description from file
        if ($this->getDescription() === '') {
            $descFile = $this->discoverGitDir() . '/' . $this->descriptionFile;

            if (\is_file($descFile)) {

                if (!\is_readable($descFile)) {
                    include_once \dirname(__FILE__) . '/../Exception.php';
                    throw new G\Repositories\Exception(
                        'repository contains a description file' .
                        ', but it\'s not readable'
                    );
                }

                $this->setDescription(
                    \trim(\strip_tags(\file_get_contents($descFile)))
                );
            }
        }
    }

    /**
     * get newest and oldest revisition id
     *
     * @param Boolean $reset reset the values from the class
     *
     * @return Array newest and oldest as keys
     */
    public function revId($reset = false)
    {
        if ((null === $this->newestRevisitionId
            && null === $this->oldestRevisitionId)
            || true === $reset
        ) {
            $revs = $this->execCommand(
                'rev-list --all --full-history --topo-order'
            );

            $this->newestRevisitionId = $revs[0];
            $this->oldestRevisitionId = \end($revs);
        }

        return array(
            'newest' => $this->newestRevisitionId,
            'oldest' => $this->oldestRevisitionId
        );
    }

    /**
     * get the default git directory
     *
     * @return String git directory
     */
    public function getDefaultGitDir()
    {
        return $this->defaultGitDir;
    }

    /**
     * set default git directory
     *
     * @param String $git_dir default git dir
     *
     * @return Null
     */
    public function setDefaultGitDir($git_dir)
    {
        $this->defaultGitDir = $git_dir;
    }

    /**
     * get name of the description file
     *
     * @return String name of the description file
     */
    public function getDescriptionFile()
    {
        return $this->descriptionFile;
    }

    /**
     * set name of the description file
     *
     * @param String $decription_file name of the description file
     *
     * @return Null
     */
    public function setDescriptionFile($decription_file)
    {
        $this->descriptionFile = $decription_file;
    }

    /**
     * get location of the git excutable
     *
     * @return String path to git
     */
    public function getBinLocation()
    {
        return $this->binLocation;
    }

    /**
     * set location of the git excutable
     *
     * @param String $bin_location path to git
     *
     * @return Null
     */
    public function setBinLocation($bin_location)
    {
        $this->binLocation = $bin_location;
    }

    /**
     * get owner of the repository
     *
     * @return String owner
     */
    public function getOwner()
    {
        if ($this->owner === null) {
            $ownerString = $this->execCommand(
                'rev-list --header --max-count=1 HEAD'
            );

            foreach ($ownerString as $output) {
                if (\substr($output, 0, 9) == 'committer') {
                    $results = array();
                    \preg_match(
                        '/^ (.+) \d+ \+\d{4}$/',
                        \substr($output, 9),
                        $results
                    );

                    $owner = \trim($results[1]);
                }
            }

            $this->setOwner($owner);
        }

        return $this->owner;
    }

    /**
     * set owner of the repository
     *
     * @param String $owner owner
     *
     * @return Null
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * get last change date of the repository
     *
     * @return \DateTime DateTime object with last change date
     */
    public function getLastChange()
    {
        if ($this->lastChange === null) {

            $dateString = $this->execCommand(
                'rev-list --header --max-count=1 HEAD'
            );

            foreach ($dateString as $output) {
                if (\substr($output, 0, 9) == 'committer') {
                    $results = array();
                    \preg_match(
                        '/^ .+ (\d+) \+\d{4}$/',
                        \substr($output, 9),
                        $results
                    );

                    $date = \trim($results[1]);
                }
            }

            $dateTime = new \DateTime();
            $dateTime->setTimestamp($date);
            $this->setLastChange($dateTime);

        }

        return $this->lastChange;
    }

    /**
     * set last change date of the repository
     *
     * @param \DateTime $datetime DateTime object with last change date
     *
     * @return Null
     */
    public function setLastChange(\DateTime $datetime)
    {
        $this->lastChange = $datetime;
    }

    /**
     * get branches
     *
     * @return Array branches
     */
    public function getBranches()
    {
        if (false === $this->showBranches()) {
            $this->branches = array(array('name' => 'master', 'default' => 0));
            return $this->branches;
        }

        if (0 === \count($this->branches)) {
            $branchesString = $this->execCommand('branch');

            $branches = array();
            foreach ($branchesString as $branch) {
                if ('*' === \substr($branch, 0, 1)) {
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

        return $this->branches;
    }

    /**
     * set branches
     *
     * @param Array $branches branches as array
     *
     * @return Null
     */
    public function setBranches($branches)
    {
        $this->branches = $branches;
    }

    /**
     * get newest revisition id
     *
     * @return String newest id
     */
    public function getNewestRevisitionId()
    {
        list($newest, ) = \array_values($this->revId());
        return $newest;
    }

    /**
     * get updates file since a version id
     *
     * @param String $uid version id
     *
     * @return Array an array containing files
     */
    public function getUpdateFiles($uid)
    {
        list($newest, $oldest) = \array_values($this->revId());
        $files = $this->execCommand(
            'diff -M -C --name-status '. $uid . ' ' . $newest
        );
        return $this->parseFiles($files);
    }

    /**
     * get alles files from the repository
     *
     * @return Array an array containing files
     */
    public function getInstallFiles()
    {
        list($newest, $oldest) = \array_values($this->revId());
        $files = $this->execCommand('ls-files --full-name');

        $this->deleted = array();
        $this->modified  = array();
        $this->copied = array();
        $this->renamed = array();
        $this->added = $files;

        return array(
            'deleted'  => $this->deleted,
            'modified' => $this->modified,
            'copied'   => $this->copied,
            'renamed'  => $this->renamed,
            'added'    => $this->added
        );
    }

    /**
     * get handle of a file in the repository
     *
     * @param String $file file name
     *
     * @return Resource file hanlde
     * @return String   file source
     * @todo support for file that are not pysically
     */
    public function getFile($file)
    {
        $handle = \fopen($this->getPath() . '/' . $file, 'r');
        return $handle;
    }
}
