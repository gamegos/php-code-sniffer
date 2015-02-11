<?php
chdir(dirname(__DIR__));

$binaries = array(
    'phpcs',
    'phpcbf',
    'phpcs-pre-commit'
);
foreach ($binaries as $binary) {
    $command = sprintf('php -d phar.readonly=0 scripts/create-phar.php  %s', $binary);
    $output = array();
    $return = null;
    exec($command, $output, $return);
    if ($return != 0 || !empty($output)) {
        echo implode(PHP_EOL, $output) . PHP_EOL;
        exit(1);
    }
}
