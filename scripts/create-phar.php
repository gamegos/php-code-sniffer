<?php
chdir(dirname(__DIR__));

$binary = $argv[1];

$scriptFilename = "scripts/{$binary}.php";
$pharFilename   = "bin/{$binary}.phar";
$binaryFilename = "bin/{$binary}";

if (file_exists($pharFilename)) {
    Phar::unlinkArchive($pharFilename);
}
if (file_exists($binaryFilename)) {
    Phar::unlinkArchive($binaryFilename);
}

$phar = new Phar(
    $pharFilename,
    FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO,
    $binary
);
$phar->startBuffering();

$directories = array(
    'src',
    'vendor',
    'scripts'
);

foreach ($directories as $dirname) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirname));
    while ($iterator->valid()) {
        if ($iterator->isFile()) {
            $path = $iterator->getPathName();
            if ('php' == strtolower($iterator->getExtension())) {
                $contents = php_strip_whitespace($path);
                $phar->addFromString($path, $contents);
            } else {
                $phar->addFile($path);
            }
        }
        $iterator->next();
    }
}

$stub = "#!/usr/bin/env php\n"
      . $phar->createDefaultStub($scriptFilename);
$phar->setStub($stub);

$phar->compressFiles(Phar::GZ);

$phar->stopBuffering();

rename($pharFilename, $binaryFilename);
chmod($binaryFilename, 0775);
