<?php
namespace Lumi\CLI\Commands;

interface CommandInterface
{
    public static function run($options);
}
