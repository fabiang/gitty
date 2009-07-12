<?php
/**
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
class Gitty_Git_Command
{
    static function exec($command, $path, $config)
    {
        $global = $config->global;
        $out = array();
        $result = exec(sprintf('GIT_DIR=%s %s %s',
                            escapeshellarg($path . '/' . $global['git']['defaultGitDir']),
                            $global['git']['binLocation'],
                            $command), $out);

        return $out;
    }

    static function STATUS () {
        return 'status';
    }

    static function BRANCHES () {
        return 'branch';
    }

    static function CONFIG () {
        return 'config -z -l';
    }

    static function REVLIST_OWNER()
    {
        return 'rev-list --header --max-count=1 HEAD';
    }

    static function REVLIST_LAST_CHANGE()
    {
        return 'rev-list --header --max-count=1 HEAD';
    }

    static function REVLIST_ORDER_DESC()
    {
        return 'rev-list --all --full-history --topo-order';
    }

    static function REVLIST_GET_LATEST()
    {
        return 'rev-list --all --full-history --topo-order --max-count=1';
    }

    static function DIFF($revId, $newest)
    {
        return 'diff -M -C --name-status '. $revId . ' ' . $newest;
    }

    static function SHORT_DIFF($revId, $newest)
    {
        return 'diff --shortstat '. $revId . ' ' . $newest;
    }

    static function EXPORT($dest)
    {
        return 'checkout-index -a -f --prefix='.$dest;
    }

    static function CLONEREPO($dest)
    {
        return 'clone '.$dest;
    }

    static function CHECKOUT($branch)
    {
        return 'checkout '.$branch;
    }

    static function BRANCH($name)
    {
        return 'branch '.$name;
    }
}