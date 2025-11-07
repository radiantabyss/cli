<?php
namespace RA\CLI\Builders;

use RA\CLI\Commands as Command;
use RA\CLI\Console;

class NeutralinoBuilder implements BuilderInterface
{
    private static $options = [
        'front' => false,
        'pack' => false,
        'fast' => false,
    ];

    // vite build && node before-build.js && neu build --config-file=neutralino.config.build.json && node after-build.js

    public static function run($options) {
        self::$options = array_merge(self::$options, $options);

        self::setVersion();
        self::bundle();
        self::buildFront();
        self::copyFront();
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

    private static function bundle() {
        if ( self::$options['fast'] ) {
            return;
        }

        Console::log('bundling js');
        shell_exec('npx vite build');
        Console::log('bundle finished');

        //check if build was successful
        if ( !abs_file_exists('front') ) {
            throw new \Exception('vite build failed.');
        }

        Console::log('build finished');
    }

    private static function buildFront($force = false) {
        if ( !self::$options['front'] && !$force ) {
            return;
        }

        Console::log('building front');

        //check if front path is defined correctly
        if ( !abs_file_exists($_ENV['FRONT_PATH']) ) {
            throw new \Exception('FRONT_PATH is invalid, folder not found.');
        }

        $cwd = getcwd();
        chdir($cwd.'/'.$_ENV['FRONT_PATH']);
        shell_exec('ra build');
        chdir($cwd);

        if ( !abs_file_exists($_ENV['FRONT_PATH'].'/dist') ) {
            throw new \Exception('vue build failed.');
        }

        Console::log('finished building front');
    }

    private static function copyFront() {
        if ( self::$options['fast'] ) {
            return;
        }

        Console::log('copying front');

        if ( !abs_file_exists($_ENV['FRONT_PATH'].'/dist') ) {
            Console::log('front is not built');
            self::buildFront();
        }

        //check if front path is defined correctly
        if ( !abs_file_exists($_ENV['FRONT_PATH']) ) {
            throw new \Exception('FRONT_PATH is invalid, folder not found.');
        }

        copy_recursive($_ENV['FRONT_PATH'].'/dist', 'front');

        //move static folders outside so it isn't included in asar
        /*
        $files = scandir('front');
        @mkdir('static');
        foreach ( $files as $file ) {
            if ( in_array($file, ['.', '..', 'index.html', 'assets', 'sprites.svg']) ) {
                continue;
            }

            copy_recursive('front/'.$file, 'static/'.$file);
            delete_recursive('front/'.$file);
        }*/

        Console::log('finished copying front');
    }

    private static function build() {
        shell_exec('npx neu build --config-file=neutralino.config.build.json');

        $neutralino_config = decode_json(abs_file_get_contents('neutralino.config.json'));
        $dist_path = 'dist/'.$neutralino_config['applicationName'];

        ensure_dir($dist_path.'/bin');
        ensure_dir($dist_path.'/downloads');
        ensure_dir($dist_path.'/symlinks');
        copy_recursive('configs-build', $dist_path.'/configs');
        copy_recursive('service-config', $dist_path.'/service-config');

        abs_unlink($dist_path.'.zip');

        //delete platform exes if not used
        $platforms = explode(',', $_ENV['PLATFORMS'] ?? '');
        $available_platforms = [
            'linux_arm64', 'linux_armhf', 'linux_x64',
            'mac_arm64', 'mac_universal', 'mac_x64',
            'win_x64',
        ];

        foreach ( $available_platforms as $platform ) {
            if ( !in_array($platform, $platforms) ) {
                if ( $platform == 'win_x64' ) {
                    $platform .= '.exe';
                }

                abs_unlink($dist_path.'/'.$neutralino_config['applicationName'].'-'.$platform);
            }
        }

        if ( count($platforms) == 1 ) {
            if ( $platforms[0] == 'win_x64' ) {
                $platforms[0] .= '.exe';
            }

            abs_rename($dist_path.'/'.$neutralino_config['applicationName'].'-'.$platforms[0], $dist_path.'/'.$neutralino_config['applicationName'].'.exe');
        }
    }

    private static function publish() {
        Console::log('publishing');
        //something

        // child_process.execSync('zip -r ${neutralino_config.applicationName}.zip ${neutralino_config.applicationName}', {
        //     cwd: 'dist'
        // });
    }
}
