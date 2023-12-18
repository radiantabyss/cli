<?php
namespace Lumi\CLI\Commands;

use Lumi\CLI\Console;
use Lumi\CLI\Config;

class BundleCommand implements CommandInterface
{
    private static $options;
    private static $cwd;
    private static $bundle;
    private static $bundles = [

    ];

    public static function run($options) {
        self::$cwd = getcwd().(in_array($_SERVER['SCRIPT_NAME'], ['a', 'artisan']) ? '/test' : '');
        self::$options = $options;
        self::$bundle = $options[2] ?? '';

        if ( isset($options['help']) ) {
            return self::help();
        }

        if ( in_array($_SERVER['SCRIPT_NAME'], ['a', 'artisan']) ) {
            mkdir(self::$cwd);
        }

        if ( !self::validate() ) {
            return;
        }

        if ( !self::validateDirectory() ) {
            return;
        }

        if ( !self::download() ) {
            return;
        }

        if ( !self::extract() ) {
            return;
        }

        if ( !self::copy() ) {
            return;
        }

        self::installDependencies();
        self::runPostInstallCommands();

        if ( in_array($_SERVER['SCRIPT_NAME'], ['a', 'artisan']) ) {
            delete_recursive(self::$cwd);
        }

        echo Console::green('Success!');
    }

    private static function help() {
        echo Console::normal('This command will copy the selected bundle from ')
            .Console::light_purple('https://github.com/radiantabyss/lumi-bundles')
            .Console::normal(' into the current directory and augment the current project.')."\n"
            .Console::normal('Example: ').Console::green('lumi bundle vue-ssr').Console::normal(' will copy the contents of ')
            .Console::light_purple('https://github.com/radiantabyss/lumi-bundles/archive/refs/heads/vue.zip')
            .Console::normal(' into the current directory.')."\n"
            .Console::normal('Note: If the directory is not Vue or Laravel project (depending on the bundle) the command will not continue unless ')
            .Console::yellow('--force')
            .Console::normal(' parameter is added.')."\n"
            ."\n"
            .Console::normal('Available bundles:')."\n";

        foreach ( array_keys(self::$bundles) as $bundle ) {
            echo Console::green($bundle)."\n";
        }
    }

    private static function validate() {
        if ( in_array(self::$bundle, array_keys(self::$bundles)) ) {
            return true;
        }

        echo Console::red('Error: ').Console::normal('Bundle is not valid.')."\n\n"
            .Console::normal('Available bundles:')."\n";

        foreach ( array_keys(self::$bundles) as $bundle ) {
            echo Console::green($bundle)."\n";
        }

        return false;
    }

    private static function validateDirectory() {
        $files = array_filter(scandir(self::$cwd), function($file) {
            return !in_array($file, ['.', '..', '.git', '.gitignore']);
        });

        if ( preg_match('/^vue/', self::$bundle) && !in_array('vue.config.js', $files) && !isset(self::$options['force']) ) {
            echo Console::red('Error: ').Console::normal('Directory is not a Vue project. Use ').Console::yellow('--force').Console::normal(' ignore current contents.');
            return false;
        }
        else if ( preg_match('/^laravel/', self::$bundle) && !in_array('bootstrap', $files) && !isset(self::$options['force']) ) {
            echo Console::red('Error: ').Console::normal('Directory is not a Laravel project. Use ').Console::yellow('--force').Console::normal(' ignore current contents.');
            return false;
        }

        return true;
    }

    private static function download() {
        $url = 'https://github.com/radiantabyss/lumi-bundles/archive/refs/heads/'.self::$bundle.'.zip';

        if ( !is_writable(self::$cwd) ) {
            Console::error(self::$cwd.' is not writable or does not exist.');
            return false;
        }

        $zip_file = self::$cwd.'/bundle.zip';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $data = curl_exec($ch);
        curl_close($ch);

        file_put_contents($zip_file, $data);

        return true;
    }

    private static function extract() {
        $zip_file = self::$cwd.'/bundle.zip';
        $zip = new \ZipArchive;

        try {
            $zip->open($zip_file);
            $zip->extractTo(self::$cwd);
            $zip->close();
            unlink($zip_file);
        }
        catch(\Exception $e) {
            Console::error($e->getMessage());
            return false;
        }

        return true;
    }

    private static function copy() {
        copy_recursive(self::$cwd.'/lumi-bundles-'.self::$bundle.'/'.self::$bundle, self::$cwd);
        delete_recursive(self::$cwd.'/lumi-bundles-'.self::$bundle);

        return true;
    }

    private static function installDependencies() {
        $grouped_dependencies = Config::get('bundle-dependencies')[self::$bundle] ?? [];

        foreach ( $grouped_dependencies as $type => $dependencies ) {
            foreach ( $dependencies as $dependency ) {
                if ( $type == 'composer' ) {
                    $command = 'composer require '.$dependency;
                }
                else if ( $type == 'composer_dev' ) {
                    $command = 'composer require '.$dependency.' --dev';
                }
                else if ( $type == 'npm' ) {
                    $command = 'npm install '.$dependency;
                }
                else if ( $type == 'npm_dev' ) {
                    $command = 'npm install '.$dependency.' --save-dev';
                }

                shell_exec($command);
            }
        }
    }

    private static function runPostInstallCommands() {
        $commands = Config::get('bundle-post-install-commands')[self::$bundle] ?? [];

        foreach ( $commands as $command ) {
            shell_exec($command);
        }
    }
}
