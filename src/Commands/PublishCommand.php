<?php
namespace Lumi\CLI\Commands;

class PublishCommand implements CommandInterface
{
    public static function run($options) {
        $Publisher = '\\Lumi\\VueArtisan\\Publishers\\'.pascal_case($_ENV['PUBLISHER']).'Publisher';
        $Publisher::run($options);
    }
}
