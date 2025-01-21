<?php
namespace RA\CLI\Builders;

use RA\CLI\Commands as Command;
use RA\CLI\Console;

class ElectronBuilder
{
    private static $options = [
        'build-front' => false,
        'pack' => false,
    ];

    public static function run($options) {
        self::$options = array_merge(self::$options, $options);

        self::setVersion();
        self::copyFront();
        self::fixSprites();
        self::build();
        self::publish();

        return true;
    }

    private static function setVersion() {
        if ( !isset(self::$options['version']) ) {
            return;
        }

        Console::log('setting version to '.self::$options['version']);
        $contents = abs_file_get_contents('package.json');
        $contents = preg_replace('/"version"\: ".*?"/', '"version": "'.self::$options['version'].'"', $contents);
        abs_file_put_contents('package.json', $contents);
    }

    private static function copyFront() {
        Console::log('copying front');

        //check if front path is defined correctly
        if ( !abs_file_exists($_ENV['FRONT_PATH']) ) {
            throw new \Exception('FRONT_PATH is invalid, folder not found.');
        }

        //if front isn't built, build it
        if ( !abs_file_exists($_ENV['FRONT_PATH'].'/release') || self::$options['build-front'] ) {
            Console::log('front is not built. building');
            $cwd = getcwd();
            chdir($cwd.'/'.$_ENV['FRONT_PATH']);
            shell_exec('ra build');
            chdir($cwd);
        }

        if ( !abs_file_exists($_ENV['FRONT_PATH'].'/release') ) {
            throw new \Exception('Vue Builder failed.');
        }

        copy_recursive($_ENV['FRONT_PATH'].'/release', 'front');

        //change index.php to index.html
        abs_rename('front/index.php', 'front/index.html');
        Console::log('finished copying front');
    }

    private static function fixSprites() {
        Console::log('fixing sprites');

        $files = scandir(getcwd().'/front/assets');
        foreach ( $files as $file ) {
            if ( preg_match('/SpriteComponent/', $file) ) {
                break;
            }
        }

        $contents = abs_file_get_contents('front/assets/'.$file);
        $contents = str_replace('/sprites.svg?v=${t.version}', '', $contents);
        abs_file_put_contents('front/assets/'.$file, $contents);

        //add sprites.svg contents to index.html
        $sprites = abs_file_get_contents('front/sprites.svg');
        $contents = abs_file_get_contents('front/index.html');
        $contents = str_replace('</body>', $sprites.'</body>', $contents);
        abs_file_put_contents('front/index.html', $contents);
    }

    private static function build() {
        Console::log('building');

        //use env prod
        abs_rename('.env', '.env.temp');
        abs_rename('.env.prod', '.env');

        //build
        $response = shell_exec('npx electron-builder build'.(self::$options['pack'] ? ' --dir' : ''));

        //restore env
        abs_rename('.env', '.env.prod');
        abs_rename('.env.temp', '.env');

        if ( preg_match('/The process cannot access the file because it is being used by another process/', $response) ) {
            throw new \Exception('Electron Builder failed. app.asar is locked by another process.');
        }

        if ( preg_match('/remove .*?: Access is denied./', $response) ) {
            throw new \Exception('Electron Builder failed. Another instance of the app is open.');
        }

        //check if build was successful
        if ( !abs_file_exists('dist') ) {
            throw new \Exception('Electron Builder failed.');
        }

        delete_recursive('front');
        Console::log('build finished');
    }

    private static function publish() {
        Console::log('publishing');
        //something
    }
}
