<?php
$phar = new Phar('lumi.phar');
$phar->startBuffering();
$phar->addFile('src/.php');
$phar->setStub("#!/usr/bin/env php\n" . $phar->createDefaultStub('my_cli.php'));
$phar->stopBuffering();
