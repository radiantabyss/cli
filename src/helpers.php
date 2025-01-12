<?php
function dmp($text, $text2 = null) {
    $pre = true;
    if ( php_sapi_name() == 'cli' ) {
        $pre = false;
    }

    if ( $pre ) {
        echo '<pre>';
    }

    if ( $text2 !== null ) {
        echo $text.': ';
        var_dump($text2);
    }
    else {
        var_dump($text);
    }

    if ( $pre ) {
        echo '</pre>';
    }
    else {
        echo "\n";
    }
}

function ddmp($text) {
    dmp($text);die();
}

function delete_recursive($directory) {
    foreach(glob("{$directory}/*") as $file) {
        if ( is_dir($file) ) {
            delete_recursive($file);
        }
        else {
            @unlink($file);
        }
    }

    if ( !glob("{$directory}/*") ) {
        foreach( glob("{$directory}/.*") as $file ) {
            if ( $file == $directory.'/.' || $file == $directory.'/..' ) continue;

            @unlink($file);
        }
    }

    @rmdir($directory);
}

function copy_recursive($source, $dest) {
    $source = str_replace(getcwd().'/', '', $source);
    $source = getcwd().'/'.$source;

    $dest = str_replace(getcwd().'/', '', $dest);
    $dest = getcwd().'/'.$dest;

    // Check for symlinks
    if ( is_link($source) ) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if ( is_file($source) ) {
        return copy($source, $dest);
    }

    // Make destination directory
    if ( !is_dir($dest) ) {
        mkdir($dest);
    }

    // Loop through the folder
    $dir = dir($source);
    while ( false !== $entry = $dir->read() ) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        copy_recursive("$source/$entry", "$dest/$entry");
    }

    // Clean up
    $dir->close();
    return true;
}

function get_files_recursive(string $directory, array $allFiles = [], $absolute_path = true) {
    if ( $absolute_path ) {
        $directory = str_replace(getcwd().'/', '', $directory);
        $directory = getcwd().'/'.$directory;
    }

    $files = array_diff(scandir($directory), ['.', '..']);

    foreach ($files as $file) {
        $fullPath = $directory. DIRECTORY_SEPARATOR .$file;

        if( is_dir($fullPath) ) {
            $allFiles += get_files_recursive($fullPath, $allFiles, $absolute_path);
        }
        else {
            $allFiles[] = $fullPath;
        }
    }

    return $allFiles;
}

function snake_case($str) {
    return strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', lcfirst($str)));
}

function pascal_case($str) {
    return str_replace(' ', '', ucwords(str_replace(['-', '_', ':'], ' ', $str)));
}

function command_exists($command) {
    $is_windows = strpos(PHP_OS, 'WIN') === 0;
    $response = shell_exec(($is_windows ? 'where ' : 'which ').$command);
    if ( $is_windows && preg_match('/Could not find files for the given pattern/', $response) ) {
        return false;
    }

    if ( !$is_windows && !$response ) {
        return false;
    }

    return true;
}

function decode_json($string) {
    if (gettype($string) == 'string') {
        return json_decode($string, true);
    }

    return $string;
}

function encode_json($array, $null_if_empty = true) {
    if ( gettype($array) == 'string' ) {
        return $array;
    }

    if ( $array === null || !count($array) ) {
        return $null_if_empty ? null : json_encode([]);
    }

    return json_encode($array);
}

function plural($str) {
    if ( preg_match('/y$/', $str) ) {
        return preg_replace('/y$/', 'ies', $str);
    }

    if ( preg_match('/s$/', $str) ) {
        return $str.'es';
    }

    return $str.'s';
}

function random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $string;
}

function abs_file_get_contents($file, $encoding = 'UTF-8') {
    return file_get_contents(getcwd().'/'.$file, $encoding);
}

function abs_file_put_contents($file, $contents) {
    return file_put_contents(getcwd().'/'.$file, $contents);
}

function abs_file_exists($file) {
    return file_exists(getcwd().'/'.$file);
}
