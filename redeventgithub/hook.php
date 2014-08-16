<?php
/**
 * This script should be called by the github hook
 */

error_reporting(E_ALL);
$str = file_get_contents("php://input");

$convert = rawurldecode($str);

parse_str($convert);

print_r($payload);

if (strstr($_SERVER['SERVER_NAME'], 'play'))
{
	$targetBranch = 'maersk-version-qa';
	$basepath = '/home/play';
}
else
{
	$targetBranch = 'maersk-main';
	$basepath = '/home/staging';
}

// Update repo
$cmd = 'cd ' . $basepath . '/git/redEVENT2.5 2<&1; git fetch --all 2<&1; ';
$cmd .= 'git reset --hard origin/' . $targetBranch . ' 2<&1; ';
$cmd .= 'git submodule update 2<&1; ';

// Build
$cmd .= 'phing 2<&1; ';

// Update db
$cmd .= 'php ' . $basepath . '/public_html/redeventgithub/redInstall.php --extension=redevent; ';
$cmd .= 'php ' . $basepath . '/public_html/redeventgithub/redInstall.php --extension=redeventsync; ';

$output = shell_exec($cmd);

echo $output;
