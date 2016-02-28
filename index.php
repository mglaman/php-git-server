<?php
const APP_ROOT = __DIR__;
require APP_ROOT . '/vendor/autoload.php';

$application = new \PhpGitServer();
$application->run();
