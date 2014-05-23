<?php
/**
 * This script should be called by the github hook
 */

error_reporting(E_ALL);
$str = file_get_contents("php://input");

$convert = rawurldecode($str);

parse_str($convert);

$targetBranch = 'maersk-main';

// Update repo
$cmd = 'cd /home/staging/git/redEVENT2.5 2<&1; git fetch --all 2<&1; ';
$cmd .= 'git reset --hard origin/' . $targetBranch . ' 2<&1; ';
$cmd .= 'git submodule update 2<&1; ';

// Build
$cmd .= 'phing 2<&1; ';

// Update db
$cmd .= 'php /home/staging/public_html/redeventgithub/redInstall.php --extension=redevent; ';
$cmd .= 'php /home/staging/public_html/redeventgithub/redInstall.php --extension=redeventsync; ';

$output = shell_exec($cmd);

echo $output;
