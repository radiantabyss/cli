<?php
namespace RA\CLI\Commands;

use RA\CLI\Console;

class BoilerplateCommand implements CommandInterface
{
    private static $options;
    private static $cwd;
    private static $boilerplate;
    private static $boilerplates = [
        'laravel', 'vue', 'electron',
    ];

    public static function run($options) {
        self::$cwd = getcwd().(in_array($_SERVER['SCRIPT_NAME'], ['a', 'artisan']) ? '/test' : '');
        self::$options = $options;
        self::$boilerplate = $options[0] ?? '';

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
            .Console::light_purple('https://github.com/radiantabyss/sources')
            .Console::normal(' into the current directory.')."\n"
            .Console::normal('Example: ').Console::green('ra boilerplate vue').Console::normal(' will copy the contents of ')
            .Console::light_purple('https://github.com/radiantabyss/sources/archive/refs/heads/vue.zip')
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
        $url = 'https://github.com/radiantabyss/sources/archive/refs/heads/'.self::$boilerplate.'.zip';

        if ( !is_writable(self::$cwd) ) {
            Console::error(self::$cwd.' is not writable or does not exist.');
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $data = curl_exec($ch);
        curl_close($ch);

        abs_file_put_contents('boilerplate.zip', $data);

        return true;
    }

    private static function extract() {
        $zip = new \ZipArchive;

        try {
            $zip->open('boilerplate.zip');
            $zip->extractTo(self::$cwd);
            $zip->close();
            unlink(self::$cwd.'/boilerplate.zip');
        }
        catch(\Exception $e) {
            Console::error($e->getMessage());
            return false;
        }

        return true;
    }

    private static function copy() {
        copy_recursive(self::$cwd.'/sources-'.self::$boilerplate.'/'.self::$boilerplate, '');
        delete_recursive(self::$cwd.'/sources-'.self::$boilerplate);

        return true;
    }
}
