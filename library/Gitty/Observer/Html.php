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
 * @package  Html
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Observer/Html
 */

/**
 * @namespace Gitty
 */
namespace Gitty\Observer;

/**
 * short hands
 */
use \Gitty\Deployment as Deployment;

/**
 * observer class
 *
 * @category Gitty
 * @package  Html
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Observer/Html
 */
class Html implements ObserverInterface
{
    /**
     * deployment is started
     */
    protected $started = false;

    /**
     * flush output to server
     *
     * @return Null
     */
    protected function flush()
    {
        if (false !== \ob_get_length()) {
            \ob_flush();
            \flush();
        }
    }

    /**
     * constructor
     */
    public function __construct()
    {
    }

    /**
     * destuctor flushes
     */
    public function __destruct()
    {
        $this->flush();
    }

    /**
     * start event function
     *
     * @param Gitty\Deployment $deployment deployment object
     *
     * @return Null
     */
    public function onStart(Deployment $deployment)
    {
        $this->started = true;
        $this->flush();

        print '<p>Starting ';
        if ($deployment->install) {
            print 'installation';
        } else {
            print 'update';
        }
        print ' process.</p>';

        $this->flush();
    }

    /**
     * event when remote is up-to-date
     *
     * @param Gitty\Deployment $deployment deployment object
     *
     * @return Null
     */
    public function onUpToDate(Deployment $deployment)
    {
        print '<p>The remote is up-to-date. Cancel.</p>';
        $this->flush();
    }

    /**
     * statistics event
     *
     * @param Gitty\Deployment $deployment deployment object
     * @param Array            $files      files array
     *
     * @return Null
     */
    public function onStat(Deployment $deployment, $files)
    {
        $all_count = \count($files['added']) +
                     \count($files['modified']) +
                     \count($files['copied']) +
                     \count($files['renamed']) +
                     \count($files['deleted']);
        \printf(
            '<p>%d files (%d added, %d modified,\
            %d copied, %d renamed, %d deleted)</p>',
            $all_count,
            \count($files['added']),
            \count($files['modified']),
            \count($files['copied']),
            \count($files['renamed']),
            \count($files['deleted'])
        );
        $this->flush();
    }

    /**
     * when transaction is finished
     *
     * @param Gitty\Deployment $deployment deployment object
     *
     * @return Null
     */
    public function onEnd(Deployment $deployment)
    {
        if (true === $this->started) {
            $start = $deployment->start;
            $interval = $start->diff(new \DateTime());
            \printf(
                '<p>Everything done. Operation took %s.</p>',
                $interval->format('%i minutes and %s seconds')
            );
            $this->flush();
        }
    }

    /**
     * start adding files
     *
     * @param Gitty\Deployment $deployment deployment object
     *
     * @return Null
     */
    public function onAddStart(Deployment $deployment)
    {
        print '<p>Start adding files...</p><ul>'.
        $this->flush();
    }

    /**
     * file is beeing added
     *
     * @param Gitty\Deployment $deployment deployment object
     * @param String           $file       file name
     *
     * @return Null
     */
    public function onAdd(Deployment $deployment, $file)
    {
        print "<li>adding $file</li>";
        $this->flush();
    }

    /**
     * file adding is finished
     *
     * @param Gitty\Deployment $deployment deployment object
     *
     * @return Null
     */
    public function onAddEnd(Deployment $deployment)
    {
        print '</ul>';
        $this->flush();
    }

    /**
     * modified files are added
     *
     * @param Gitty\Deployment $deployment deployment object
     *
     * @return Null
     */
    public function onModifiedStart(Deployment $deployment)
    {
        print '<p>modified files...</p><ul>'.
        $this->flush();
    }

    /**
     * file that was modified
     *
     * @param Gitty\Deployment $deployment deployment object
     * @param String           $file       file name
     *
     * @return Null
     */
    public function onModified(Deployment $deployment, $file)
    {
        print "<li>$file</li>";
        $this->flush();
    }

    /**
     * modified files adding ended
     *
     * @param Gitty\Deployment $deployment deployment object
     *
     * @return Null
     */
    public function onModifiedEnd(Deployment $deployment)
    {
        print '</ul>';
        $this->flush();
    }

    /**
     * start deleting files
     *
     * @param Gitty\Deployment $deployment deployment object
     *
     * @return Null
     */
    public function onDeletedStart(Deployment $deployment)
    {
        print '<p>deleting files...</p><ul>'.
        $this->flush();
    }

    /**
     * file is beeing deleted
     *
     * @param Gitty\Deployment $deployment deployment object
     * @param String           $file       file name
     *
     * @return Null
     */
    public function onDeleted(Deployment $deployment, $file)
    {
        print "<li>delete $file</li>";
        $this->flush();
    }

    /**
     * deleting files ended
     *
     * @param Gitty\Deployment $deployment deployment object
     *
     * @return Null
     */
    public function onDeletedEnd(Deployment $deployment)
    {
        print '</ul>';
        $this->flush();
    }

    /**
     * start renaming files
     *
     * @param Gitty\Deployment $deployment deployment object
     *
     * @return Null
     */
    public function onRenamedStart(Deployment $deployment)
    {
        print '<p>rename files...</p><ul>'.
        $this->flush();
    }

    /**
     * file is beeing deleted
     *
     * @param Gitty\Deployment $deployment deployment object
     * @param String           $file       old file name
     * @param String           $new        new file name
     *
     * @return Null
     */
    public function onRenamed(Deployment $deployment, $file, $new)
    {
        print "<li>rename $file to $new</li>";
        $this->flush();
    }

    /**
     * renaming files has finished
     *
     * @param Gitty\Deployment $deployment deployment object
     *
     * @return Null
     */
    public function onRenamedEnd(Deployment $deployment)
    {
        print '</ul>';
        $this->flush();
    }
}
