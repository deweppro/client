<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 08.01.18
 * Time: 1:20
 */

namespace Dewep\Client;


/**
 * @author Mikhail Knyazhev <markus621@gmail.com>
 */
interface HttpInterface
{
    /**
     * @param array $data
     * @return HttpInterface
     */
    public function methodGet(array $data = []): HttpInterface;

    /**
     * @return string
     */
    public function getUrl(): string;

    /**
     * @param string $url
     * @return HttpInterface
     */
    public function setUrl(string $url): HttpInterface;

    /**
     * @param array $data
     * @return HttpInterface
     */
    public function methodHead(array $data = []): HttpInterface;

    /**
     * @param array $data
     * @return HttpInterface
     */
    public function methodDelete(array $data = []): HttpInterface;

    /**
     * @param mixed $body
     * @return HttpInterface
     */
    public function methodPost($body): HttpInterface;

    /**
     * @param mixed $body
     * @return HttpInterface
     */
    public function methodPut($body): HttpInterface;

    /**
     * @param int $timeout
     * @return HttpInterface
     */
    public function setTimeout(int $timeout): HttpInterface;

    /**
     * @return HttpInterface
     */
    public function sslOn(): HttpInterface;

    /**
     * @return HttpInterface
     */
    public function sslOff(): HttpInterface;

    /**
     * @param string $value
     * @return HttpInterface
     */
    public function setUserAgent(string $value): HttpInterface;

    /**
     * @param string $host
     * @param int $port
     * @param string $login
     * @param string $passwd
     * @return HttpInterface
     */
    public function setProxy(string $host, int $port, string $login = '', string $passwd = ''): HttpInterface;

    /**
     * @param string $login
     * @param string $pwd
     * @return HttpInterface
     */
    public function setBasicAuth(string $login, string $pwd): HttpInterface;

    /**
     * @param string $key
     * @param string $value
     * @return HttpInterface
     */
    public function setHead(string $key, string $value): HttpInterface;

    /**
     * @param string $token
     * @return HttpInterface
     */
    public function setBearerAuth(string $token): HttpInterface;

    /**
     * @return HttpInterface
     */
    public function make(): HttpInterface;

    /**
     * @return string
     */
    public function getResponseError(): string;

    /**
     * @return array
     */
    public function getResponseInfo(): array;

    /**
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * @param bool $asObject
     * @return mixed
     */
    public function getResponseJson(bool $asObject = false);

    /**
     * @return mixed|null
     */
    public function getResponse();

    /**
     * @param array $namespaces
     * @param array $replace
     * @return \SimpleXMLElement
     */
    public function getResponseXml(array $namespaces = [], array $replace = []): \SimpleXMLElement;

    /**
     * @return array
     */
    public function getResponseHead(): array;

    /**
     * @return mixed|null
     */
    public function getServerIp();
}