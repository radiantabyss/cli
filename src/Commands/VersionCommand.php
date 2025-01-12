<?php
namespace RA\CLI\Commands;

use RA\CLI\Console;

class VersionCommand implements CommandInterface
{
    public static function run($options) {
        $version = '1.1.0';
        Console::log($version);
    }
}
