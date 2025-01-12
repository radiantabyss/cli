<?php
namespace Lumi\CLI\Commands;

class SpriteCommand implements CommandInterface
{
    public static function run($options) {
        self::png();
        self::svg();
    }

    private static function png() {
        shell_exec('npm run sprite');
    }

    private static function svg() {
        if ( !command_exists('svg-sprite-generate') ) {
            echo "\n\nRun \033[96mnpm i svg-sprite-generator -g\033[0m for build speed improvement.\n\n";
        }

        shell_exec((!command_exists('svg-sprite-generate') ? 'npx -p svg-sprite-generator ' : '').'svg-sprite-generate -d sprites/svgs -o static/sprites.svg');

        $sprites = abs_file_get_contents('static/sprites.svg');
        $sprites = preg_replace('/stroke="((?!none).)*?"/', 'stroke="currentColor"', $sprites);
        $sprites = preg_replace('/fill="((?!none).)*?"/', 'fill="currentColor"', $sprites);
        $sprites = str_replace('fill-static', 'fill', $sprites);
        $sprites = str_replace('stroke-static', 'stroke', $sprites);
        $sprites = str_replace(['<?xml version="1.0" encoding="utf-8"?>', '</svg>', '<svg xmlns="http://www.w3.org/2000/svg">'], '', $sprites);
        $sprites = str_replace(["\n</symbol>", "\r\n</symbol>"], "</symbol>\n", $sprites);
        $sprites = trim($sprites);

        //handle no-fill custom attribute
        $svgs = scandir('sprites/svgs');
        foreach ( $svgs as $svg ) {
            if ( in_array($svg, ['.', '..']) ) {
                continue;
            }

            $contents = abs_file_get_contents('sprites/svgs/'.$svg);

            //put back svg's attrs
            preg_match('/\<svg.*?\>/', $contents, $match);

            if ( !$match ) {
                echo "\n\nError: File ".$svg." is not formatted correctly. <svg> tag should beging and end on the same row.";
                return;
            }

            $attrs = str_replace(['<svg', '>'], '', $match[0]);
            $ignored_attrs = ['xmlns', 'xmlns:link', 'class', 'viewBox', 'width', 'height', 'version', 'id'];
            foreach ( $ignored_attrs as $ignored_attr ) {
                $attrs = preg_replace('/ '.$ignored_attr.'=".*?"/', '', $attrs);
            }
            preg_match('/\<symbol.*?id="'.str_replace('.svg', '', $svg).'".*?\>/', $sprites, $match);
            $sprites = str_replace($match[0], str_replace('symbol', 'symbol '.$attrs.' ', $match[0]), $sprites);

            //remove fill
            if ( preg_match('/no-fill/', $contents) ) {
                preg_match('/\<symbol.*?id="'.str_replace('.svg', '', $svg).'".*?\>/', $sprites, $match);
                $sprites = str_replace($match[0], str_replace('fill="currentColor" ', ' fill="none" ', $match[0]), $sprites);
            }
        }

        abs_file_put_contents('static/sprites.svg', '<svg xmlns="http://www.w3.org/2000/svg">'.$sprites.'</svg>');

        //update env file
        $env_contents = abs_file_get_contents('.env');
        $env_contents = preg_replace('/VUE_APP_SPRITE_VERSION=.*?\n/', "VUE_APP_SPRITE_VERSION=".random_string()."\n", $env_contents);
        abs_file_put_contents('.env', $env_contents);
    }
}
