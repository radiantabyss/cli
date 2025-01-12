<?php
namespace Lumi\CLI\Commands;

class SassCommand implements CommandInterface
{
    public static function run($options) {
        $contents = "@import \"abstracts/settings\";\n"
        ."@import \"abstracts/mixins\";\n\n";

        $files = get_files_recursive('src/Sass');
        $current_folder = '';

        foreach ( $files as $file ) {
            $file = str_replace('src/Sass/', '', str_replace('\\', '/', $file));
            if ( in_array($file, ['app.scss', 'abstracts/_settings.scss', 'abstracts/_mixins.scss']) ) {
                continue;
            }

            $pathinfo = pathinfo($file);
            if ( $current_folder != $pathinfo['dirname'] ) {
                $contents .= "\n";
                $current_folder = $pathinfo['dirname'];
            }

            $contents .= '@import "'.preg_replace('/\.scss$/', '', str_replace('/_', '/', $file))."\";\n";
        }

        abs_file_put_contents('src/Sass/app.scss', $contents);
    }
}
