<?php
namespace RA\CLI\Builders;

use RA\CLI\Commands as Command;

class VueBuilder implements BuilderInterface
{
    private static $options = [
        'skip-sprites' => false,
        'skip-build' => false,
        'skip-publish' => false,
        'fast' => false,
    ];

    public static function run($options) {
        self::$options = array_merge(self::$options, $options);

        Command\StaticCommand::run([]);
        Command\SassCommand::run([]);

        if ( !self::$options['skip-sprites'] && !self::$options['fast'] ) {
            Command\SpriteCommand::run([]);
        }

        self::build();
        self::ssr();
        self::htaccess();
        self::copyStatic();
        self::errorPage();
        self::publish();
    }

    private static function build() {
        if ( self::$options['skip-build'] ) {
            return;
        }

        //copy static for npx vite build
        copy_recursive('static', 'public');

        if ( !self::$options['fast'] ) {
            shell_exec('npm install');
        }

        shell_exec('npx vite build');

        if ( !abs_file_exists('dist') ) {
            throw new \Exception('Vue build failed.');
        }

    }

    private static function ssr() {
        if ( !abs_file_exists('ssr-env.php') ) {
            return;
        }

        abs_rename('dist/index.html', 'dist/index.php');
        $index_contents = abs_file_get_contents('dist/index.php');

        preg_match('/\<script type="module" crossorigin src="\/assets\/index-(.*)?\.js/', $index_contents, $match);
        $js_version = $match[1];

        preg_match('/\<link rel="stylesheet" crossorigin href="\/assets\/index-(.*)?\.css/', $index_contents, $match);
        $css_version = $match[1];

        $ssr_env_contents = abs_file_get_contents('ssr-env.php');
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

        abs_file_put_contents('ssr-env.php', $ssr_env_contents);
    }

    private static function htaccess() {
        if ( !abs_file_exists('ssr-env.php') ) {
            return;
        }

        abs_file_put_contents('dist/.htaccess', '<IfModule mod_rewrite.c>
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
        if ( !abs_file_exists('ssr-env.php') ) {
            return;
        }

        //make error page
        copy_recursive('dist/index.php', 'dist/404.php');
        $contents = abs_file_get_contents('dist/404.php');
        abs_file_put_contents('dist/404.php', str_replace('<div id=app></div>', '<div id=app>'.
        '<div class="content text-center pt-30">'.
        '    <div class="title title--small mb-20 mt-40">(404) Not found.</div>'.
        '    <div class="subtitle">We\'re sorry, the page you\'re looking for doesn\'t exist.</div>'.
        '</div></div>',
        $contents));
    }

    private static function publish() {
        if ( self::$options['skip-build'] || self::$options['skip-publish'] ) {
            return;
        }

        @abs_rename('release', 'release2');
        abs_rename('dist', 'release');
        @delete_recursive('release2');
        delete_recursive('public');
    }
}
