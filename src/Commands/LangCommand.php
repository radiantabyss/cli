<?php
namespace RA\CLI\Commands;

use RA\CLI\Console;

class LangCommand implements CommandInterface
{
    public static function run($options) {
        $terms = self::terms();
        $langs = self::langs();

        $translate = $options['translate'] ?? false;
        $translate_api_key = $_ENV['DEEPL_KEY'] ?? '';

        if ( $translate && !$translate_api_key ) {
            Console::log('Warning! DEEPL API key missing.', 'yellow');
        }

        foreach ( $langs as $lang ) {
            if ( $lang == 'en' ) {
                continue;
            }

            $contents = isset($options['force']) ? [] : decode_json(abs_file_get_contents('static/lang/'.$lang.'.json'));

            foreach ( $terms as $term ) {
                $contents[$term] = $contents[$term] ?? $term;
            }

            if ( $translate ) {
                $contents = self::translate($contents, $lang);
            }

            abs_file_put_contents('static/lang/'.$lang.'.json', json_encode($contents, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

            //check if there are any untranslated terms
            if ( isset($options['check']) ) {
                $untranslated = [];
                foreach ( $contents as $term => $translation ) {
                    if ( $term == $translation ) {
                        $untranslated[] = $term;
                    }
                }

                Console::log($lang.': '.count($untranslated));
                foreach ( $untranslated as $term ) {
                    Console::log($term);
                }
            }
        }

        if ( $translate ) {
            StaticCommand::run([]);
        }
    }

    private static function terms() {
        $files = get_files_recursive('src');
        $terms = [];

        foreach ( $files as $file ) {
            //ignore scss files
            if ( preg_match('/\.scss/', $file)  ) {
                continue;
            }

            $contents = abs_file_get_contents($file);

            $regexs = [
                '/\_\_\(\'(.*?)\'\)/', // __() function
                '/<t>(.*?)<\/t>/s', // <t> tags
            ];

            foreach ( $regexs as $regex ) {
                preg_match_all($regex, $contents, $matches);

                if ( !count($matches) || !count($matches[1]) ) {
                    continue;
                }

                foreach ( $matches[1] as $match ) {
                    //ignore terms with double curly braces
                    if ( preg_match('/\{\{/', $match) ) {
                        continue;
                    }

                    $match = str_replace("\'", "'", $match);
                    $terms[] = $match;
                }
            }
        }

        return array_unique($terms);
    }

    private static function langs() {
        $files = scandir('static/lang');
        $langs = [];

        foreach ( $files as $file ) {
            if ( preg_match('/\.json/', $file) ) {
                $langs[] = str_replace('.json', '', $file);
            }
        }

        return $langs;
    }

    private static function translate($contents, $lang) {
        $api_key = $_ENV['DEEPL_KEY'] ?? '';

        if ( !$api_key ) {
            return $contents;
        }

        $terms = [];
        foreach ( $contents as $term => $translation ) {
            //check if the key was already translated
            if ( $term != $translation ) {
                continue;
            }

            $url = 'https://api-free.deepl.com/v2/translate';

            $data = http_build_query([
                'auth_key' => $api_key,
                'text' => $term,
                'source_lang' => 'en',
                'target_lang' => $lang,
            ]);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            }

            curl_close($ch);

            if ( isset($error_msg) ) {
                Console::log('Translation Error: '.$error_msg);
                return $contents;
            }

            $result = decode_json($response);
            $translation = $result['translations'][0]['text'];

            $contents[$term] = $translation;
        }

        return $contents;
    }
}
