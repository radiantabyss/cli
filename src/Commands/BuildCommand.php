<?php
namespace Lumi\CLI\Commands;

class BuildCommand implements CommandInterface
{
    private static $options = [
        'skip-sprites' => false,
        'skip-build' => false,
        'skip-publish' => false,
        'keep-dark-mode' => false,
        'fast' => false,
    ];

    public static function run($options) {
        //set options
        self::$options = array_merge(self::$options, $options);

        //build sass
        SassCommand::run([]);

        //run sprites
        self::sprites();

        //run static
        StaticCommand::run([]);

        //build
        self::build();

        //publish
        self::publish();
    }

    private static function sprites() {
        if ( self::$options['skip-sprites'] || self::$options['fast'] ) {
            return;
        }

        SpriteCommand::run([]);
    }

    private static function build() {
        if ( self::$options['skip-build'] ) {
            return;
        }

        $Builder = '\\Lumi\\VueArtisan\\Builders\\'.pascal_case($_ENV['LUMI_BUILDER']).'Builder';
        $Builder::run(self::$options);
    }

    private static function publish() {
        if ( self::$options['skip-build'] || self::$options['skip-publish'] ) {
            return;
        }

        $Publisher = '\\Lumi\\VueArtisan\\Publishers\\'.pascal_case($_ENV['LUMI_PUBLISHER']).'Publisher';
        $Publisher::run(self::$options);
    }
}
