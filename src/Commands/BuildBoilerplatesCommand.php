<?php
namespace Lumi\CLI\Commands;

use Lumi\CLI\Console;

class BuildBoilerplatesCommand implements CommandInterface
{
    private static $cwd;
    private static $boilerplates = [
        'laravel-auth' => ['laravel-auth'],
        // 'laravel-shop' => ['laravel-auth', 'laravel-shop'],
        'vue-ssr' => ['vue-ssr'],
        'vue-auth' => ['vue-auth'],
        'vue-admin' => ['vue-auth', 'vue-admin'],
        // 'vue-shop' => ['vue-auth', 'vue-shop'],
    ];

    public static function run($options) {
        //change cwd
        self::$cwd = getcwd().'/../boilerplates';
        chdir(self::$cwd);

        foreach ( self::$boilerplates as $boilerplate => $bundles ) {
            $base_boilerplate = preg_match('/^laravel/', $boilerplate) ? 'laravel' : 'vue';

            delete_recursive($boilerplate);
            copy_recursive($base_boilerplate, $boilerplate);

            foreach ( $bundles as $bundle ) {
                copy_recursive(getcwd().'/../bundles/'.$bundle, $boilerplate);
            }
        }
    }
}
