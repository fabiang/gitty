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

    static function ADD ($file = '.') {
        if (func_num_args() > 1) {
            $files = func_get_args();

            return 'add ' . implode(' ',$files);
        }

        return "add $file";
    }

    static function COMMIT () {
        return 'commit';
    }

    static function DIFF () {
        return 'diff';
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
}