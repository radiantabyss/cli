<?php
namespace RA\CLI\Commands;

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
        self::loadEnv();
        self::$options = array_merge(self::$options, $options);
        SassCommand::run([]);
        self::sprites();
        StaticCommand::run([]);
        self::build();
        self::publish();
    }

    private static function loadEnv() {
        if ( !abs_file_exists('.env') ) {
            echo "\033[31mError:\033[0m .env file is missing.";
            return;
        }

        $lines = file(getcwd().'/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
            }
        }
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

        $Builder = '\\RA\\CLI\\Builders\\'.pascal_case($_ENV['BUILDER']).'Builder';
        $Builder::run(self::$options);
    }

    private static function publish() {
        if ( self::$options['skip-build'] || self::$options['skip-publish'] ) {
            return;
        }

        $Publisher = '\\RA\\CLI\\Publishers\\'.pascal_case($_ENV['PUBLISHER']).'Publisher';
        $Publisher::run(self::$options);
    }
}
