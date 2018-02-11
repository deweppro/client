<?php

namespace Dewep;

/**
 * @author Mikhail Knyazhev <markus621@gmail.com>
 */
class Funtion
{
    /**
     * @param string $key
     * @return string
     */
    public static function originalHttpKey($key)
    {
        if (stripos($key, 'HTTP_') === 0) {
            $key = substr($key, 5);
        }
        $key = str_replace(['-', '_'], ' ', $key);
        $key = ucwords(strtolower($key));

        return str_replace(' ', '-', trim($key));
    }

    /**
     * @param string $url
     * @param array $update
     * @return string
     */
    public static function updateUrl($url, $update)
    {
        $url = parse_url($url);

        if ($url === false) {
            $url = [];
        }

        $scheme = static::exist($update, 'scheme', static::exist($url, 'scheme', null));
        $user   = static::exist($update, 'user', static::exist($url, 'user', null));
        $pass   = static::exist($update, 'pass', static::exist($url, 'pass', null));
        $host   = static::exist($update, 'host', static::exist($url, 'host', null));
        $port   = static::exist($update, 'port', static::exist($url, 'port', null));
        $path   = static::exist($update, 'path', static::exist($url, 'path', null));
        $query  = static::exist($update, 'query', static::exist($url, 'query', null));

        if (isset($update['query'])) {
            $_query = [];
            parse_str(empty($query) ? '' : $query, $_query);

            $_query_update = [];
            if (is_string($update['query'])) {
                parse_str($update['query'], $_query_update);
            } else {
                $_query_update = is_array($update['query']) ? $update['query'] : [];
            }

            $query = http_build_query(
                array_replace_recursive(
                    $_query,
                    $_query_update
                )
            );
        }

        $userInfo = $user ? $user.($pass ? ':'.$pass : null) : null;

        $authority = ($userInfo ? $userInfo.'@' : '').$host.($port ? ':'.$port : '');

        return ($scheme ? $scheme.':' : '')
            .($authority ? '//'.$authority : '')
            .'/'.trim($path, '/')
            .($query ? '?'.$query : '');
    }

    /**
     * @param mixed $obj
     * @param string $key
     * @param mixed $defaulf
     * @return mixed
     */
    public static function exist($obj, $key, $defaulf)
    {
        if (isset($obj[$key])) {
            return $obj[$key];
        }

        return $defaulf;
    }

    /**
     * @param $body
     * @param $head
     * @return mixed|string
     */
    public static function bodyFormat($body, $head)
    {
        $contentType = empty($head['Content-Type']) ? '' : $head['Content-Type'];

        if (!is_array($body)) {
            return (string)$body;
        }

        //--
        if (stripos($contentType, 'json') !== false) {
            return json_encode($body);
        } //--
        elseif (stripos($contentType, 'x-www-form-urlencoded') !== false) {
            return http_build_query($body);
        } //--
        elseif (stripos($contentType, 'xml') !== false) {
            $xml = new \SimpleXMLElement('<body/>');
            array_walk_recursive($body, array($xml, 'addChild'));

            return $xml->asXML();
        }
    }

}
