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
}
