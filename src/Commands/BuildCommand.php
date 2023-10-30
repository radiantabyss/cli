<?php
namespace Lumi\CLI\Commands;

class BuildCommand implements CommandInterface
{
    public static function run($options) {
        try {
            self::build();
        }
        catch(\UnexpectedValueException $e) {
            $ok = shell_exec('php -d phar.readonly=0 artisan build');
            echo $ok;
        }
    }

    private static function build() {
        $files = get_files_recursive('src');
        $files = array_merge($files, get_files_recursive('vendor'));
        $files[] = 'artisan';

        $phar = new \Phar('lumi.phar');
        $phar->startBuffering();

        foreach ( $files as $file ) {
            $file = str_replace('\\', '/', $file);

            //ignore these files
            $ignored_files = [
                'src/Commands/BuildCommand.php',
                'src/Commands/BuildBoilerplatesCommand.php',
                'src/Commands/PublishCommand.php'
            ];

            if ( in_array($file, $ignored_files) ) {
                continue;
            }

            $phar->addFile($file);
        }

        $phar->setStub("#!/usr/bin/env php\n" . $phar->createDefaultStub('artisan'));
        $phar->stopBuffering();

        rename('lumi.phar', 'dist/lumi.phar');
    }
}
