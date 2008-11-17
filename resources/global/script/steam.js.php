<?php

require_once '../../../config.php';

$script = '
var steam_base_uri = "' . $base_uri . '";
var steam_api_uri  = "' . $base_uri . '/api";
';

header('Content-Type: text/javascript');
header('Content-Length: ' . strlen($script));
header('Pragma: private');
header('Cache-Control: private');
header('Last-Modified: '. gmdate('D, d M Y H:i:s', filemtime($_SERVER['SCRIPT_FILENAME'])).' GMT');
header('Expires: ' . gmdate("D, d M Y H:i:s", time() + 86400) . ' GMT');
echo $script;
?>

