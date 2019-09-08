<?php declare(strict_types=1);

namespace Dewep\Client\Traits;

/**
 * Trait UrlTrait
 *
 * @package Dewep\Client\Traits
 */
trait UrlTrait
{

    /**
     * @param string $url
     * @param array  $data
     *
     * @return string
     */
    protected function queryUpdate(string $url, array $data): string
    {
        if (empty($data)) {
            return $url;
        }

        $arr = parse_url($url);

        if ($arr === false) {
            $arr = [];
        }

        $scheme = $arr['scheme'] ?? null;
        $user = $arr['user'] ?? null;
        $pass = $arr['pass'] ?? null;
        $host = $arr['host'] ?? null;
        $port = $arr['port'] ?? null;
        $path = $arr['path'] ?? null;

        $query = [];
        parse_str((string)($arr['query'] ?? ''), $query);

        $query = http_build_query(
            array_replace_recursive(
                $query,
                $data
            )
        );

        $userInfo = $user ? ($user.($pass ? ':'.$pass : null)) : null;

        $authority = ($userInfo ? $userInfo.'@' : '').$host.($port ? ':'.$port : '');

        return ($scheme ? $scheme.':' : '')
            .($authority ? '//'.$authority : '')
            .'/'.trim((string)$path, '/')
            .($query ? '?'.$query : '');
    }

}
