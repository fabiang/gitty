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
namespace Gitty\Observer;

/**
 * short hands
 */
use \Gitty\Deployment as Deployment;

/**
 * observer class
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Html implements ObserverInterface
{
    protected function _flush()
    {
        ob_flush();
        flush();
    }

    public function __construct()
    {
    }

    public function __destruct()
    {
        $this->_flush();
    }

    public function onStart(Deployment $deployment)
    {
        $this->_flush();

        print '<p>Starting ';
        if ($deployment->install) {
            print 'installation';
        } else {
            print 'update';
        }
        print ' process.</p>';

        $this->_flush();
    }

    public function onUpToDate(Deployment $deployment)
    {
        print '<p>The remote is up-to-date. Cancel.</p>';
        $this->_flush();
    }

    public function onStat(Deployment $deployment, $files)
    {
        $all_count = \count($files['added']) +
                     \count($files['modified']) +
                     \count($files['copied']) +
                     \count($files['renamed']) +
                     \count($files['deleted']);
        \printf('<p>%d files (%d added, %d modified, %d copied, %d renamed, %d deleted)</p>',
                $all_count,
                \count($files['added']),
                \count($files['modified']),
                \count($files['copied']),
                \count($files['renamed']),
                \count($files['deleted']));
        $this->_flush();
    }

    public function onEnd(Deployment $deployment)
    {
        $start = $deployment->start;
        $interval = $start->diff(new \DateTime());
        \printf('<p>Everything done. Operation took %s.</p>', $interval->format('%i minutes and %s seconds'));
        $this->_flush();
    }

    public function onAddStart(Deployment $deployment)
    {
        print '<p>Start adding files...</p><ul>'.
        $this->_flush();
    }

    public function onAdd(Deployment $deployment, $file)
    {
        print "<li>adding $file</li>";
        $this->_flush();
    }

    public function onAddEnd(Deployment $deployment)
    {
        print '</ul>';
        $this->_flush();
    }

    public function onModifiedStart(Deployment $deployment)
    {
        print '<p>modified files...</p><ul>'.
        $this->_flush();
    }

    public function onModified(Deployment $deployment, $file)
    {
        print "<li>$file</li>";
        $this->_flush();
    }

    public function onModifiedEnd(Deployment $deployment)
    {
        print '</ul>';
        $this->_flush();
    }

    public function onDeletedStart(Deployment $deployment)
    {
        print '<p>deleting files...</p><ul>'.
        $this->_flush();
    }

    public function onDeleted(Deployment $deployment, $file)
    {
        print "<li>delete $file</li>";
        $this->_flush();
    }

    public function onDeletedEnd(Deployment $deployment)
    {
        print '</ul>';
        $this->_flush();
    }

    public function onRenamedStart(Deployment $deployment)
    {
        print '<p>rename files...</p><ul>'.
        $this->_flush();
    }

    public function onRenamed(Deployment $deployment, $file, $new)
    {
        print "<li>rename $file to $new</li>";
        $this->_flush();
    }

    public function onRenamedEnd(Deployment $deployment)
    {
        print '</ul>';
        $this->_flush();
    }
}
