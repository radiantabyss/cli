<?php
namespace RA\CLI\Commands;

class BuildCommand implements CommandInterface
{
    public static function run($options) {
        $Builder = '\\RA\\CLI\\Builders\\'.pascal_case($_ENV['BUILDER']).'Builder';
        $Builder::run($options);
    }
}
