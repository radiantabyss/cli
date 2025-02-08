<?php
namespace RA\CLI\Commands;

use RA\CLI\Console;
use RA\CLI\Crud;

class CrudCommand implements CommandInterface
{
    public static function run($options) {
        if ( !count($options) ) {
            echo Console::red('Error!').' Crud Domain name is required.';
            return;
        }

        $domain = $options[0];
        $force = $options['force'] ?? false;

        if ( abs_file_exists('env.php') ) {
            return self::laravel($domain, $force);
        }
        else if ( abs_file_exists('.env') ) {
            if ( preg_match('/BUILDER=electron/', abs_file_get_contents('.ra')) ) {
                return self::electron($domain, $force);
            }
            else {
                return self::vue($domain, $force);
            }
        }

        echo Console::red('Error!').' Project could not be identified. Call this command in the root folder of the project.';
    }

    private static function laravel($domain, $force) {
        $exp = explode('/', $domain);
        $exp2 = [];
        foreach ( $exp as $_exp ) {
            $exp2 = array_merge($exp2, explode('\\', $_exp));
        }

        foreach ( $exp2 as &$_exp2 ) {
            $_exp2 = pascal_case($_exp2);
        }

        $folder_path = 'app/Domains/'.implode('/', $exp2);
        $namespace = implode('\\', $exp2);
        $model_name = implode($exp2);
        $item_name = implode(' ', $exp2);

        if ( abs_file_exists($folder_path) && !$force ) {
            echo Console::red('Error!').' A Domain with this name already exists.';
            return;
        }

        Crud\Laravel::run($folder_path, $namespace, $model_name, $item_name);
    }

    private static function electron($domain, $force) {
        $exp = explode('/', $domain);
        $exp2 = [];
        foreach ( $exp as $_exp ) {
            $exp2 = array_merge($exp2, explode('\\', $_exp));
        }

        foreach ( $exp2 as &$_exp2 ) {
            $_exp2 = pascal_case($_exp2);
        }

        $folder_path = 'app/Domains/'.implode('/', $exp2);
        $namespace = implode('\\', $exp2);
        $model_name = implode($exp2);
        $item_name = implode(' ', $exp2);

        if ( abs_file_exists($folder_path) && !$force ) {
            echo Console::red('Error!').' A Domain with this name already exists.';
            return;
        }

        Crud\Electron::run($folder_path, $namespace, $model_name, $item_name);
    }

    private static function vue($domain, $force) {
        $exp = explode('/', $domain);
        $exp2 = [];
        foreach ( $exp as $_exp ) {
            $exp2 = array_merge($exp2, explode('\\', $_exp));
        }

        foreach ( $exp2 as &$_exp2 ) {
            $_exp2 = pascal_case($_exp2);
        }

        $folder_path = 'app/Domains/'.implode('/', $exp2);
        $namespace = implode('\\', $exp2);
        $item_name = implode(' ', $exp2);
        $url = '/'.implode('/', array_map(function($_exp2) {
            return str_replace('_', '-', snake_case($_exp2));
        }, $exp2));

        if ( abs_file_exists($folder_path) && !$force ) {
            echo Console::red('Error!').' A Domain with this name already exists.';
            return;
        }

        Crud\Vue::run($folder_path, $namespace, $item_name, $url);
    }
}
