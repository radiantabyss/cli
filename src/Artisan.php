<?php
namespace RA\CLI;

class Artisan
{
    private static $command;
    private static $options;

    public static function run($argv) {
        self::parseArgv($argv);

        if ( isset(self::$options['cwd']) ) {
            chdir(self::$options['cwd']);
        }

        $Command = '\\RA\CLI\\Commands\\'.pascal_case(self::$command).'Command';

        if ( !class_exists($Command) ) {
            Console::error('Command doesn\'t exist.');
            return;
        }

        $commands_without_ra_env = ['build-cli', 'boilerplate', 'bundle', 'publish-sources'];

        if ( !abs_file_exists('.ra') && !in_array(self::$command, $commands_without_ra_env) ) {
            echo "\033[31mError:\033[0m .ra file is missing.";
            die();
        }

        self::loadRA();
        self::setProjectType();

        try {
            $Command::run(self::$options);
        }
        catch(\Exception $e) {
            Console::error($e->getMessage());
        }
    }

    private static function parseArgv($argv) {
        $options = [];

        $i = -1;
        foreach ( $argv as $k => $a ) {
            $i++;
            if ( $i == 0 ) {
                continue;
            }

            if ( $i == 1 ) {
                self::$command = $a;
                continue;
            }

            if ( preg_match('/\-\-(.+)=(.+)/', $a, $m) ) {
                $options[$m[1]] = $m[2];
            }
            else if ( preg_match('/\-\-(.+)/', $a, $m) ) {
                $options[$m[1]] = true;
            }
            else {
                $options[] = $a;
            }
        }

        self::$options = $options;
    }

    private static function loadRA() {
        if ( !abs_file_exists('.ra') ) {
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

    private static function setProjectType() {
        $project_type = '';

        if ( abs_file_exists('env.php') ) {
            $project_type = 'laravel';
        }
        else if ( abs_file_exists('.env') ) {
            if ( preg_match('/BUILDER=electron/', abs_file_get_contents('.ra')) ) {
                $project_type = 'laravel';
            }
            else {
                $project_type = 'vue';
            }
        }

        $_ENV['PROJECT_TYPE'] = $project_type;
    }
}
