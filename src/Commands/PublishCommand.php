<?php
namespace RA\CLI\Commands;

class PublishCommand implements CommandInterface
{
    public static function run($options) {
        $Publisher = '\\RA\\CLI\\Publishers\\'.pascal_case($_ENV['PUBLISHER']).'Publisher';
        $Publisher::run($options);
    }
}
