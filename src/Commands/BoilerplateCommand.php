<?php
namespace Lumi\CLI\Commands;

use Lumi\CLI\Console;

class BoilerplateCommand implements CommandInterface
{
    private static $options;
    private static $cwd;
    private static $boilerplate;
    private static $boilerplates = [
        'laravel', 'laravel-auth', 'laravel-shop',
        'vue', 'vue-ssr', 'vue-admin', 'vue-shop',
    ];

    public static function run($options) {
        self::$cwd = getcwd().(in_array($_SERVER['SCRIPT_NAME'], ['a', 'artisan']) ? '/test' : '');
        self::$options = $options;
        self::$boilerplate = $options[2] ?? '';

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

        if ( in_array($_SERVER['SCRIPT_NAME'], ['a', 'artisan']) ) {
            delete_recursive(self::$cwd);
        }

        echo Console::green('Success!');
    }

    private static function help() {
        echo Console::normal('This command will copy the selected boilerplate from ')
            .Console::light_purple('https://github.com/radiantabyss/lumi-boilerplates')
            .Console::normal(' into the current directory.')."\n"
            .Console::normal('Example: ').Console::green('lumi boilerplate vue').Console::normal(' will copy the contents of ')
            .Console::light_purple('https://github.com/radiantabyss/lumi-boilerplates/archive/refs/heads/vue.zip')
            .Console::normal(' into the current directory.')."\n"
            .Console::normal('Note: If the directory is not empty the command will not continue unless ')
            .Console::yellow('--force')
            .Console::normal(' parameter is added.')."\n"
            ."\n"
            .Console::normal('Available boilerplates:')."\n";

        foreach ( self::$boilerplates as $boilerplate ) {
            echo Console::green($boilerplate)."\n";
        }
    }

    private static function validate() {
        if ( in_array(self::$boilerplate, self::$boilerplates) ) {
            return true;
        }

        echo Console::red('Error: ').Console::normal('Boilerplate is not valid.')."\n\n"
            .Console::normal('Available boilerplates:')."\n";

        foreach ( self::$boilerplates as $boilerplate ) {
            echo Console::green($boilerplate)."\n";
        }

        return false;
    }

    private static function validateDirectory() {
        $cwd_is_empty = empty(array_filter(scandir(self::$cwd), function($file) {
            return !in_array($file, ['.', '..', '.git', '.gitignore']);
        }));

        if ( !$cwd_is_empty && !isset(self::$options['force']) ) {
            echo Console::red('Error: ').Console::normal('Directory is not empty. Use ').Console::yellow('--force').Console::normal(' ignore current contents.');
            return false;
        }

        return true;
    }

    private static function download() {
        $url = 'https://github.com/radiantabyss/lumi-boilerplates/archive/refs/heads/'.self::$boilerplate.'.zip';

        if ( !is_writable(self::$cwd) ) {
            Console::error(self::$cwd.' is not writable or does not exist.');
            return false;
        }

        $zip_file = self::$cwd.'/boilerplate.zip';

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
        $zip_file = self::$cwd.'/boilerplate.zip';
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
        copy_recursive(self::$cwd.'/lumi-boilerplates-'.self::$boilerplate.'/'.self::$boilerplate, self::$cwd);
        delete_recursive(self::$cwd.'/lumi-boilerplates-'.self::$boilerplate);

        return true;
    }
}
