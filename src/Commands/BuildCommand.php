<?php
namespace RA\CLI\Commands;

class BuildCommand implements CommandInterface
{
    public static function run($options) {
        self::loadRA();

        $Builder = '\\RA\\CLI\\Builders\\'.pascal_case($_ENV['BUILDER']).'Builder';
        $Builder::run($options);
    }

    private static function loadRA() {
        if ( !abs_file_exists('.ra') ) {
            echo "\033[31mError:\033[0m .ra file is missing.";
            return;
        }

        $lines = file(getcwd().'/.ra', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
        }
    }
}
