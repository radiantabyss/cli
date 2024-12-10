<?php
namespace Lumi\CLI\Commands;

class BuildCliCommand implements CommandInterface
{
    public static function run($options) {
        try {
            $files = get_files_recursive('src');
            $files = array_merge($files, get_files_recursive('vendor'));
            $files[] = 'artisan';

            $phar = new \Phar('lumi.phar');
            $phar->startBuffering();

            foreach ( $files as $file ) {
                $file = str_replace('\\', '/', $file);

                //ignore these files
                $ignored_files = [
                    'src/Commands/BuildCliCommand.php',
                    'src/Commands/BuildBoilerplatesCommand.php',
                    'src/Commands/PublishBundlesOrBoilerplatesCommand.php'
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
        catch(\UnexpectedValueException $e) {
            $ok = shell_exec('php -d phar.readonly=0 artisan build-cli');
            echo $ok;
        }
    }
}
