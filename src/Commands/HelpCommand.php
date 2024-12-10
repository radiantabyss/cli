<?php
namespace Lumi\CLI\Commands;

use Lumi\CLI\Console;

class HelpCommand implements CommandInterface
{
    public static function run($options) {
        Console::log('Commands list:');
        echo "\n";
        echo Console::green('version')."\n";
        echo "\n";

        echo Console::green('build').Console::grey(str_repeat('.', 16)).Console::normal('Builds the code.')."\n";
        echo str_repeat(' ', 2).Console::yellow('--fast').str_repeat(' ', 13).Console::grey('Skips \'npm install\' and sprites when building')."\n";
        echo str_repeat(' ', 2).Console::yellow('--skip-sprites')."\n";
        echo str_repeat(' ', 2).Console::yellow('--skip-build')."\n";
        echo str_repeat(' ', 2).Console::yellow('--skip-publish')."\n";
        echo str_repeat(' ', 2).Console::yellow('--version').str_repeat(' ', 10).Console::grey('Required for Electron')."\n";
        echo "\n";

        echo Console::green('publish').Console::grey(str_repeat('.', 14)).Console::normal('Publishes the build (release or upload). Called automatically when ').Console::green('build').Console::normal('ing.')."\n";
        echo "\n";

        echo Console::green('sass').Console::grey(str_repeat('.', 17)).Console::normal('Generates app.scss file. Called automatically when ').Console::green('build').Console::normal('ing.')."\n";
        echo "\n";

        echo Console::green('sprite').Console::grey(str_repeat('.', 15)).Console::normal('Generates sprites. Called automatically when ').Console::green('build').Console::normal('ing.')."\n";
        echo "\n";

        echo Console::green('static').Console::grey(str_repeat('.', 15)).Console::normal('Copies static assets to public folder. Called automatically when ').Console::green('build').Console::normal('ing.')."\n";
        echo "\n";

        echo Console::green('lang').Console::grey(str_repeat('.', 17)).Console::normal('Parses translated text from src and puts them in lang/{lang}.json')."\n";
        echo str_repeat(' ', 2).Console::yellow('--translate').str_repeat(' ', 8).Console::grey('Translates using DeepL. Requires an api key.')."\n";
        echo "\n";

        echo Console::green('boilerplate ').Console::light_cyan(' <boilerplate_name>').str_repeat(' ', 3).Console::normal(' Copies a Lumi Boilerplate in the current working folder.')."\n";
        echo str_repeat(' ', 6).Console::yellow('--help').str_repeat(' ', 23).Console::grey('displays more info about the command')."\n";

        echo Console::green('bundle ').Console::light_cyan(' <bundle_name>').str_repeat(' ', 3).Console::normal(' Copies a Lumi Bundle in the current working folder.')."\n";
        echo str_repeat(' ', 6).Console::yellow('--help').str_repeat(' ', 23).Console::grey('displays more info about the command')."\n";
    }
}
