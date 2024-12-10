<?php
namespace Lumi\CLI\Builders;

class ElectronBuilder
{
    private static $options;

    public static function run($options) {
        self::$options = $options;

        //check for required options
        if ( !isset($options['version']) ) {
            throw new \Exception('Electron Builder Error: version is required. Example: --version=1.0.0');
        }

        //set version
        self::setVersion();

        //build
        self::build();

        return true;
    }

    private static function setVersion() {
        $package_json = file_get_contents('package.json');
        file_put_contents('package.json', preg_replace('/"version"\: ".*?"/', '"version": "'.self::$options['version'].'"', $package_json));
    }

    private static function build() {
        //clean previous
        delete_recursive('dist_electron');

        //get app name from vue config
        preg_match('/productName\: \'(.*)?\'\,/', file_get_contents('vue.config.js'), $match);
        $app_name = $match[1];

        //get vue config contents
        $vue_config = file_get_contents('vue.config.js');

        //set env to production
        $env_contents = file_get_contents('.env');
        file_put_contents('.env', str_replace('VUE_APP_ENV=local', 'VUE_APP_ENV=production', $env_contents));

        $archs = ['ia32', 'x64'];
        foreach ( $archs as $arch ) {
            //set arch
            file_put_contents('vue.config.js', preg_replace("/'nsis.*?'/", "'nsis:$arch'", $vue_config));

            //run build
            shell_exec('npm run electron:build');

            //rename installer
            rename('dist_electron/'.$app_name.' Setup '.self::$options['version'].'.exe',
                'dist_electron/'.$app_name.' Setup '.self::$options['version'].($arch == 'ia32' ? '-x32' : '').'.exe');
        }

        //restore vue config
        file_put_contents('vue.config.js', $vue_config);

        //restore env to local
        file_put_contents('.env', str_replace('VUE_APP_ENV=production', 'VUE_APP_ENV=local', $env_contents));

        if ( !file_exists('dist_electron') ) {
            throw new \Exception('Electron Builder failed.');
        }
    }

}
