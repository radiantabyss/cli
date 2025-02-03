<?php
namespace RA\CLI\Commands;

class SassCommand implements CommandInterface
{
    public static function run($options) {
        $contents = "@import \"abstracts/settings\";\n"
        ."@import \"abstracts/mixins\";\n";

        $files = get_files_recursive('app/Sass');
        $current_folder = '';

        //place vendor at the top
        foreach ( $files as $file ) {
            $file = str_replace(getcwd().'/', '', $file);
            $file = str_replace('app/Sass/', '', str_replace('\\', '/', $file));

            if ( in_array($file, ['app.scss', 'abstracts/_settings.scss', 'abstracts/_mixins.scss']) ) {
                continue;
            }

            if ( !preg_match('/^vendor/', $file) ) {
                continue;
            }

            $pathinfo = pathinfo($file);
            if ( $current_folder != $pathinfo['dirname'] ) {
                $contents .= "\n";
                $current_folder = $pathinfo['dirname'];
            }

            $contents .= '@import "'.preg_replace('/\.scss$/', '', str_replace('/_', '/', $file))."\";\n";
        }

        //add the rest of the files
        foreach ( $files as $file ) {
            $file = str_replace(getcwd().'/', '', $file);
            $file = str_replace('app/Sass/', '', str_replace('\\', '/', $file));

            if ( in_array($file, ['app.scss', 'abstracts/_settings.scss', 'abstracts/_mixins.scss']) ) {
                continue;
            }

            if ( preg_match('/^vendor/', $file) ) {
                continue;
            }

            $pathinfo = pathinfo($file);
            if ( $current_folder != $pathinfo['dirname'] ) {
                $contents .= "\n";
                $current_folder = $pathinfo['dirname'];
            }

            $contents .= '@import "'.preg_replace('/\.scss$/', '', str_replace('/_', '/', $file))."\";\n";
        }

        abs_file_put_contents('app/Sass/app.scss', $contents);
    }
}
