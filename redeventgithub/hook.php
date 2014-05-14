<?php
/**
 * This script should be called by the github hook
 */

error_reporting(E_ALL);
$str = file_get_contents("php://input");

$convert = rawurldecode($str);

parse_str($convert);

$targetBranch = 'maersk-overrides';

// Update repo
$cmd = 'cd /home/staging/git/redEVENT2.5; git fetch --all; ';
$cmd .= 'git reset --hard origin/' . $targetBranch . '; ';
$cmd .= 'git submodule update; ';
$cmd .= 'git describe; ';

// Build
$cmd .= 'phing 2<&1; ';

// Update db
$cmd .= 'php /home/staging/public_html/redeventgithub/redInstall.php; ';
$cmd .= 'php /home/staging/public_html/redeventgithub/redInstallResync.php; ';

$output = shell_exec($cmd);

echo $output;
