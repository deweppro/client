<?php

namespace Dewep;

/**
 * Description of Funtion
 *
 * @author Mikhail Knyazhev <markus621@gmail.com>
 */
class Funtion
{

    public static function originalHttpKey(string $key): string
    {
        if (stripos($key, 'HTTP_') === 0) {
            $key = substr($key, 5);
        }
        $key = str_replace(['-', '_'], ' ', $key);
        $key = ucwords(strtolower($key));
        return str_replace(' ', '-', trim($key));
    }

    public static function updateUrl(string $url, array $update): string
    {
        $url = parse_url($url);

        if ($url === false) {
            $url = [];
        }

        $scheme = $update['scheme'] ?? $url['scheme'] ?? null;
        $user = $update['user'] ?? $url['user'] ?? null;
        $pass = $update['pass'] ?? $url['pass'] ?? null;
        $host = $update['host'] ?? $url['host'] ?? null;
        $port = $update['port'] ?? $url['port'] ?? null;
        $path = $update['path'] ?? $url['path'] ?? null;
        $query = $url['query'] ?? null;

        if (isset($update['query'])) {
            $_query = [];
            parse_str($query ?? '', $_query);

            $_query_update = [];
            if (is_string($update['query'])) {
                parse_str($update['query'], $_query_update);
            } else {
                $_query_update = is_array($update['query']) ? $update['query'] : [
                        ];
            }

            $query = http_build_query(array_replace_recursive($_query,
                            $_query_update));
        }

        $userInfo = $user ? $user . ($pass ? ':' . $pass : null) : null;

        $authority = ($userInfo ? $userInfo . '@' : '') . $host . ($port ? ':' . $port : '');

        return ($scheme ? $scheme . ':' : '')
                . ($authority ? '//' . $authority : '')
                . '/' . trim($path, '/')
                . ($query ? '?' . $query : '');
    }

    public static function bodyFormat($body, $head)
    {
        $contentType = $head['Content-Type'] ?? '';

        if (!is_array($body)) {
            return $body;
        }

        //--
        if (stripos($contentType, 'json') !== false) {
            return json_encode($body);
        }
        //--
        elseif (stripos($contentType, 'x-www-form-urlencoded') !== false) {
            return http_build_query($body);
        }
        //--
        elseif (stripos($contentType, 'xml') !== false) {
            $xml = new \SimpleXMLElement('<body/>');
            array_walk_recursive($body, array($xml, 'addChild'));
            return $xml->asXML();
        }
    }

}
