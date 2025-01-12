<?php
namespace RA\CLI\Publishers;

class ElectronPublisher implements PublisherInterface
{
    private static $options;
    private static $name;
    private static $version;

    public static function run($options) {
        //set options
        self::$options = $options;

        //set app name
        self::setName();

        //set version
        self::setVersion();

        //copy installer to cdn folder
        self::copyInstaller();

        //zip and copy asar
        self::zipAsar();

        //copy and archive new/updated static files
        self::copyUpdatedStaticFiles();

        //make filezilla xml files for server upload
        self::makeFilezillaXML();
    }

    private static function setName() {
        //get app name from vue config
        preg_match('/productName\: \'(.*)?\'\,/', abs_file_get_contents('vue.config.js'), $match);
        self::$name = $match[1];
    }

    private static function setVersion() {
        //get version from package.json
        preg_match('/"version"\: "(.*)?"/', abs_file_get_contents('package.json'), $match);
        self::$version = $match[1];
    }

    private static function copyInstaller() {
        $archs = ['ia32', 'x64'];
        foreach ( $archs as $arch ) {
            copy('dist_electron/'.self::$name.' Setup '.self::$version.($arch == 'ia32' ? '-x32' : '').'.exe',
                '../files_cdn/public/'.self::$name.' Setup '.self::$version.($arch == 'ia32' ? '-x32' : '').'.exe');
        }
    }

    private static function zipAsar() {
        //zip asar
        $cwd = getcwd();
        chdir('dist_electron/win-unpacked/resources');
        shell_exec('zip -r app.zip app.asar');
        chdir($cwd);
        rename('dist_electron/win-unpacked/resources/app.zip', '../files_cdn/public/app.zip');
    }

    private static function copyUpdatedStaticFiles() {
        mkdir('latest');

        $files = get_files_recursive('updated-static-files');
        foreach ( $files as $file ) {
            if ( $file == 'README.md' ) {
                continue;
            }

            mkdir(pathinfo('latest/'.$file)['dirname'], 0777, true);
            copy($file, 'latest/'.$file);
        }

        shell_exec('zip -r latest.zip latest');
        rename('latest.zip', '../files_cdn/public/latest.zip');
        delete_recursive('latest');
    }

    private static function makeFilezillaXML() {
        $servers = [];
        $env_servers = explode(';', $_ENV['PUBLISHER_SERVERS']);
        foreach ( $env_servers as $env_server ) {
            $exp = explode('::', $env_server);
            $servers[] = [
                'ip' => $exp[0],
                'path' => $exp[1],
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?><FileZilla3 version="3.33.0" platform="windows"><Queue>';

        $archs = ['ia32', 'x64'];
        foreach ( $servers as $i => $server ) {
            $xml .= '<Server>
                <Host>'.$server['ip'].'</Host>
                <Port>22</Port><Protocol>1</Protocol>
                <Type>0</Type><User>root</User><Pass encoding="base64"></Pass>
                <Logontype>1</Logontype><TimezoneOffset>0</TimezoneOffset>
                <PasvMode>MODE_DEFAULT</PasvMode><MaximumMultipleConnections>0</MaximumMultipleConnections>
                <EncodingType>Auto</EncodingType><BypassProxy>0</BypassProxy>';

            $path = '1 0 ';
            $exp = explode('/', trim($server['path'], '/'));
            foreach ( $exp as $e ) {
                $path .= strlen($e).' '.$e.' ';
            }

            $path = trim($path);

            foreach ( $archs as $arch ) {
                $xml .= '<File>
                <LocalFile>'.getcwd().'/../files_cdn/public/'.self::$name.' Setup '.self::$version.($arch == 'ia32' ? '-x32' : '').'.exe</LocalFile>
                <RemoteFile>'.self::$name.' Setup '.self::$version.($arch == 'ia32' ? '-x32' : '').'.exe</RemoteFile>
                <RemotePath>'.$path.'</RemotePath>
                <Download>0</Download><DataType>1</DataType></File>';
            }

            if ( $i == 0 ) {
                $xml .= '<File>
                    <LocalFile>'.getcwd().'/../files_cdn/public/app.zip</LocalFile>
                    <RemoteFile>app.zip</RemoteFile>
                    <RemotePath>'.$path.'</RemotePath>
                    <Download>0</Download><DataType>1</DataType></File>';

                $xml .= '<File>
                    <LocalFile>'.getcwd().'/../files_cdn/public/latest.zip</LocalFile>
                    <RemoteFile>latest.zip</RemoteFile>
                    <RemotePath>'.$path.'</RemotePath>
                    <Download>0</Download><DataType>1</DataType></File>';
            }

            $xml .= '</Server>';
        }

        $xml .= '</Queue></FileZilla3>';
        abs_file_put_contents('../filezilla.xml', $xml);
    }
}
