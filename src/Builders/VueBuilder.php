<?php
namespace Lumi\CLI\Builders;

class VueBuilder implements BuilderInterface
{
    private static $options;

    public static function run($options) {
        //set options
        self::$options = $options;

        //build
        self::build();

        //rename index.html to index.php
        rename('dist/index.html', 'dist/index.php');

        //write index.php
        self::ssr();

        //create htaccess file for apache servers
        self::htaccess();

        //copy static content
        self::copyStatic();

        //set error page
        self::errorPage();
    }

    private static function build() {
        if ( !self::$options['fast'] ) {
            shell_exec('npm install');
        }

        shell_exec('npm run build');

        if ( !file_exists('dist') ) {
            throw new \Exception('Vue Builder failed.');
        }
    }

    private static function ssr() {
        if ( !file_exists('ssr-env.php') ) {
            return;
        }

        $index_contents = file_get_contents('dist/index.php');

        preg_match('/\<script type="module" crossorigin src="\/assets\/index-(.*)?\.js/', $index_contents, $match);
        $js_version = $match[1];

        preg_match('/\<link rel="stylesheet" crossorigin href="\/assets\/index-(.*)?\.css/', $index_contents, $match);
        $css_version = $match[1];

        $ssr_env_contents = file_get_contents('ssr-env.php');
        $ssr_env_contents = preg_replace(
            [
                "/'APP_CSS' => '.*?'/",
                "/'APP_JS' => '.*?'/",
                "/'BACK_URL' => '.*?'/",
                "/'UPLOADS_URL' => '.*?'/",
            ],
            [
                "'APP_CSS' => '".$css_version."'",
                "'APP_JS' => '".$js_version."'",
                "'BACK_URL' => '".$_ENV['VITE_BACK_URL']."'",
                "'UPLOADS_URL' => '".$_ENV['VITE_UPLOADS_URL']."'",
            ],
            $ssr_env_contents
        );

        file_put_contents('ssr-env.php', $ssr_env_contents);
    }

    private static function htaccess() {
        file_put_contents('dist/.htaccess', '<IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /
            RewriteRule ^index\.php$ - [L]
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule . /index.php [L]
        </IfModule>
        ');
    }

    private static function copyStatic() {
        copy_recursive('static', 'dist');
    }

    private static function errorPage() {
        //make error page
        copy('dist/index.php', 'dist/404.php');
        $contents = file_get_contents('dist/404.php');
        file_put_contents('dist/404.php', str_replace('<div id=app></div>', '<div id=app>'.
        '<div class="content text-center pt-30">'.
        '    <div class="title title--small mb-20 mt-40">(404) Not found.</div>'.
        '    <div class="subtitle">We\'re sorry, the page you\'re looking for doesn\'t exist.</div>'.
        '</div></div>',
        $contents));
    }
}
