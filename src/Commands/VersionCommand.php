<?php
namespace Lumi\CLI\Commands;

use Lumi\CLI\Console;

class VersionCommand implements CommandInterface
{
    public static function run($options) {
        $version = '1.0.0';
        Console::log($version);
    }
}
