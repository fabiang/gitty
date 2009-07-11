<?php require '../bootstrap.php'; ?>
<!DOCTYPE html>

<html>

    <head>
        <title>Gitty - Web deployment made graphically</title>
        <meta http-equiv="Content-Type" value="text/html;charset=utf-8">

        <link rel="icon" href="git-favicon.png" type="image/png" />
        <link rel="stylesheet" href="gitty.css" type="text/css" media="screen,projection" />
    </head>

    <body>
        <div id="gittyBody">

            <div id="gittyHead">
                <h1>Gitty - Web deployment made graphically</h1>
                <p><a href="http://gitty.lubyte.de/"><img src="gitty.png" alt="Gitty" ></a></p>
            </div>

            <div id="gittyContent">
<?php
$config = new Gitty_Config($_SERVER['GITTY_CONFIG']);
$deploy = new Gitty_Deploy($config);
$projectId = isset($_REQUEST['update']) ? (int)$_REQUEST['update'] : (int)$_REQUEST['install'];

$project = $_REQUEST['project'][$projectId];
$deploy->setProjectId($projectId);
$deploy->setBranchId($project['branch']);
$deploy->setDeploymentId((int)$project['remote']);
?>
                <ul>
<?php
$deploy->start();
$deploy->setCallback('callback');
if (isset($_REQUEST['install'])) {
    $deploy->install();
}
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
                <p>Gitty Version <?php print Gitty_Version::VERSION; ?></p>
            </div>

        </div>
    </body>

</html>