<?php
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
}