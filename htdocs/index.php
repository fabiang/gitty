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
                <form action="console.php">
                    <table>
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Description</th>
                                <th>Owner</th>
                                <th>Last Change</th>
                                <th>Branches</th>
                                <th>Remote</th>
                                <th colspan="2"></th>
                            </tr>
                        </thead>
<?php
$config = new Gitty\Config(getenv('GITTY_CONFIG'));
$gitty = new Gitty\Git($config);

foreach ($gitty->getRepositories() as $i => $repository): ?>
                        <tbody class="<?php if ($i % 2): ?>gittyTableOdd<?php else: ?>gittyTableEven<?php endif; ?>">
                            <tr>
                                <td><?php print htmlspecialchars($repository->name); ?></td>
                                <td><?php print htmlspecialchars($repository->description); ?></td>
                                <td><?php print htmlspecialchars($repository->owner); ?></td>
                                <td><?php print $repository->lastChange; ?></td>
                                <td>
                                    <select name="project[<?php print $i ?>][branch]">
<?php foreach($repository->branches as $branch): ?>
                                        <option<?php if ($branch['default']): ?> selected="selected"<?php endif; ?>><?php print $branch['name'] ?></option>
<?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="project[<?php print $i ?>][remote]">
<?php foreach($repository->remotes as $j => $remote): ?>
                                        <option value="<?php print $j ?>"><?php print $j; ?>: <?php print $remote['method']; ?> (Host: <?php print $remote['hostname']; ?>, User: <?php print $remote['username']; ?>, Path: <?php print $remote['path']; ?>)</option>
<?php endforeach; ?>
                                    </select>
                                </td>
                                <td><button name="update" value="0">update</button></td>
                                <td><button name="install" value="0">install</button></td>
                            </tr>
                        </tbody>
<?php endforeach;

unset($config, $gitty);
?>
                    </table>
                </form>
            </div>

            <div id="gittyFooter">
                <p>Gitty Version <?php print Gitty\Version::VERSION; ?></p>
            </div>

        </div>

    </body>

</html>