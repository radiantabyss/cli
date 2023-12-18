<?php
namespace Lumi\CLI\Commands;

use Lumi\CLI\Console;
use Lumi\CLI\Config;

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
                self::installDependencies($boilerplate, $bundle);
            }
        }
    }

    private static function installDependencies($boilerplate, $bundle) {
        $grouped_dependencies = Config::get('bundle-dependencies')[$bundle] ?? [];

        foreach ( $grouped_dependencies as $type => $dependencies ) {
            foreach ( $dependencies as $dependency ) {
                if ( preg_match('/composer/', $type) ) {
                    $contents = decode_json(file_get_contents(self::$cwd.'/'.$boilerplate.'/composer.json'));
                    $exp = explode(':', $dependency);
                    $contents['require'.($type == 'composer_dev' ? '-dev' : '')][$exp[0]] = $exp[1];
                    file_put_contents(self::$cwd.'/'.$boilerplate.'/composer.json', json_encode($contents, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                }
                else if ( preg_match('/npm/', $type) ) {
                    $contents = decode_json(file_get_contents(self::$cwd.'/'.$boilerplate.'/package.json'));
                    $exp = explode('@', $dependency);
                    if ( count($exp) == 3 ) {
                        $exp[0] = '@'.$exp[1];
                        $exp[1] = $exp[2];
                    }

                    $contents[($type == 'npm_dev' ? 'devD' : 'd').'ependencies'][$exp[0]] = $exp[1];
                    file_put_contents(self::$cwd.'/'.$boilerplate.'/package.json', json_encode($contents, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                }
            }
        }
    }
}
