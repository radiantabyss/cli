<?php
require 'src/helpers.php';

$files = get_files_recursive('src');
$files = array_merge($files, get_files_recursive('vendor'));
$files[] = 'lumi.php';

$phar = new Phar('lumi.phar');
$phar->startBuffering();

foreach ( $files as $file ) {
    $phar->addFile($file);
}

$phar->setStub("#!/usr/bin/env php\n" . $phar->createDefaultStub('lumi.php'));
$phar->stopBuffering();

rename('lumi.phar', 'dist/lumi.phar');
