<?php
namespace RA\CLI\Builders;

use RA\CLI\Commands as Command;

class VuetralinoBuilder implements BuilderInterface
{
    private static $options = [
        'skip-sprites' => false,
        'fast' => false,
    ];

    public static function run($options) {
        self::$options = array_merge(self::$options, $options);
        copy_recursive('static', 'public');

        Command\StaticCommand::run([]);
        Command\SassCommand::run([]);

        if ( !self::$options['skip-sprites'] && !self::$options['fast'] ) {
            Command\SpriteCommand::run([]);
        }

        self::build();
        copy_recursive('static', 'dist');
    }

    private static function build() {
        //copy static for npx vite build
        copy_recursive('static', 'public');

        if ( !self::$options['fast'] ) {
            shell_exec('npm install');
        }

        shell_exec('npx vite build');

        if ( !abs_file_exists('dist') ) {
            throw new \Exception('Vue build failed.');
        }
    }
}
