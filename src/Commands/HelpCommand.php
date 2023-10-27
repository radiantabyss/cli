<?php
namespace LumiCLI\Commands;

use LumiCLI\Console;

class HelpCommand implements CommandInterface
{
    public static function run($options) {
        Console::log('Commands list:');
        echo "\n";
        echo Console::green('boilerplate ').Console::light_cyan(' <boilerplate_name>').str_repeat(' ', 3).Console::normal(' Copies a Lumi Boilerplate in the current working folder.')."\n";
        echo str_repeat(' ', 6).Console::yellow('--help').str_repeat(' ', 23).Console::grey('displays more info about the command')."\n";
    }
}
