<?php
namespace Lumi\CLI\Publishers;

class VuePublisher implements PublisherInterface
{
    private static $options;

    public static function run($options) {
        if ( !file_exists('dist') ) {
            return;
        }

        @rename('release', 'release2');
        rename('dist', 'release');
        @delete_recursive('release2');
        delete_recursive('public');
    }
}
