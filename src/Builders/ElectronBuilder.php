<?php
namespace RA\CLI\Builders;

use RA\CLI\Commands as Command;

class ElectronBuilder
{
    private static $options;

    public static function run($options) {
        self::$options = $options;

        self::setVersion();
        self::copyFront();
        self::fixSprites();
        self::build();
        self::publish();

        return true;
    }

    private static function setVersion() {
        if ( !isset($options['version']) ) {
            return;
        }

        $contents = abs_file_get_contents('package.json');
        $contents = preg_replace('/"version"\: ".*?"/', '"version": "'.self::$options['version'].'"', $contents);
        abs_file_put_contents('package.json', $contents);
    }

    private static function copyFront() {
        //check if front path is defined correctly
        if ( !abs_file_exists($_ENV['FRONT_PATH']) ) {
            throw new \Exception('FRONT_PATH is invalid, folder not found.');
        }

        //if front isn't built, build it
        if ( !abs_file_exists($_ENV['FRONT_PATH'].'/release') ) {
            $cwd = getcwd();
            chdir($cwd.'/'.$_ENV['FRONT_PATH']);
            shell_exec('ra build');
            chdir($cwd);
        }

        if ( !abs_file_exists($_ENV['FRONT_PATH'].'/release') ) {
            return;
        }

        copy_recursive($_ENV['FRONT_PATH'].'/release', 'front');

        //change index.php to index.html
        abs_rename('front/index.php', 'front/index.html');
    }

    private static function fixSprites() {
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
        //use env prod
        abs_rename('.env', '.env.temp');
        abs_rename('.env.prod', '.env');

        //build
        shell_exec('npx electron-builder build');

        //restore env
        abs_rename('.env', '.env.prod');
        abs_rename('.env.temp', '.env');

        //check if build was successful
        if ( !abs_file_exists('dist') ) {
            throw new \Exception('Electron Builder failed.');
        }

        delete_recursive('front');
    }

    private static function publish() {
        //something
    }
}
