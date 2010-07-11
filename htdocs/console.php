<?php
/**
 * Gitty - web deployment tool
 * Copyright (C) 2009 Fabian Grutschus
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Gitty.  If not, see <http://www.gnu.org/licenses/>.
 */

require '../../gitty/bootstrap.php'; ?>
<!DOCTYPE html>

<html>

    <head>
        <title>Gitty - Web deployment made graphically</title>
        <meta http-equiv="Content-Type" value="text/html;charset=utf-8" >

        <link rel="icon" href="git-favicon.png" type="image/png" >
        <link rel="stylesheet" href="gitty.css" type="text/css" media="screen,projection" >
    </head>

    <body>
        <div id="gittyBody">

            <div id="gittyHead">
                <h1>Gitty - Web deployment made graphically</h1>
                <p><a href="http://gitty.lubyte.de/"><img src="gitty.png" alt="Gitty" ></a></p>
            </div>

            <div id="gittyContent">
<?php
$config = new Gitty\Config(getenv('GITTY_CONFIG'));
$deploy = new Gitty\Deploy($config);
$projectId = isset($_REQUEST['update']) ? (int)$_REQUEST['update'] : (int)$_REQUEST['install'];

$project = $_REQUEST['project'][$projectId];
$deploy->setProjectId($projectId);
$deploy->setBranch($project['branch']);
$deploy->setDeploymentId((int)$project['remote']);
?>
                <ul>
<?php
$deploy->start();
$deploy->setCallback('callback');
if (isset($_REQUEST['install'])) {
    $deploy->install();
}
ob_start();
$deploy->open();

function callback($deploy) {
    printf('<li>%s</li>', $deploy->message());
    flush();
    ob_flush();
}

$deploy->close();
?>
                </ul>
                <p><a href="index.php">zurück</a></p>
<?php
unset($deploy);
?>
            </div>

            <div id="gittyFooter">
                <p>Gitty Version <?php print Gitty\Version::VERSION; ?></p>
            </div>

        </div>
    </body>

</html>
