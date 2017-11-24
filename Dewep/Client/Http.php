<?php

namespace Dewep\Client;

use Dewep\Funtion;

/**
 * @author Mikhail Knyazhev <markus621@gmail.com>
 */
class Http
{

    const HTTP_METHOD_HEAD = 'HEAD';
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_PUT = 'PUT';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_DELETE = 'DELETE';

    protected $logger;
    protected $head = [
            //'Content-Type' => 'application/json; charset=utf-8'
    ];
    protected $config = [
        CURLOPT_URL => 'http://localhost',
        CURLOPT_USERAGENT => 'DewepClient/1.0; +https://bitbucket.org/deweppro/client',
        CURLOPT_RETURNTRANSFER => true,
        CURLINFO_HEADER_OUT => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_VERBOSE => true,
        CURLOPT_HEADER => true,
        CURLINFO_HEADER_OUT => true,
        CURLOPT_CRLF => true,
        CURLOPT_FRESH_CONNECT => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => [],
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_TIMEOUT => 30,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
    ];
    protected $body;
    protected $response = [];

    public function __construct($logger = null)
    {
        $this->logger = $logger;
    }

    /**
     *
     * @param array $data
     * @return \Dewep\Client\Http
     */
    public function methodGet(array $data = []): Http
    {
        $url = Funtion::updateUrl($this->getUrl(), ['query' => $data]);

        $this->config[CURLOPT_CUSTOMREQUEST] = static::HTTP_METHOD_GET;

        return $this->setUrl($url);
    }

    /**
     *
     * @param array $data
     * @return \Dewep\Client\Http
     */
    public function methodHead(array $data = []): Http
    {
        $url = Funtion::updateUrl($this->getUrl(), ['query' => $data]);

        $this->config[CURLOPT_CUSTOMREQUEST] = static::HTTP_METHOD_HEAD;

        return $this->setUrl($url);
    }

    /**
     *
     * @param array $data
     * @return \Dewep\Client\Http
     */
    public function methodDelete(array $data = []): Http
    {
        $url = Funtion::updateUrl($this->getUrl(), ['query' => $data]);

        $this->config[CURLOPT_CUSTOMREQUEST] = static::HTTP_METHOD_DELETE;

        return $this->setUrl($url);
    }

    /**
     *
     * @param type $body
     * @return \Dewep\Client\Http
     */
    public function methodPost($body = []): Http
    {
        $this->body = $body;

        $this->config[CURLOPT_CUSTOMREQUEST] = static::HTTP_METHOD_POST;
        $this->config[CURLOPT_POSTFIELDS] = Funtion::bodyFormat($this->body,
                        $this->head);

        return $this;
    }

    /**
     *
     * @param type $body
     * @return \Dewep\Client\Http
     */
    public function methodPut($body = []): Http
    {
        $this->body = $body;

        $this->config[CURLOPT_CUSTOMREQUEST] = static::HTTP_METHOD_PUT;
        $this->config[CURLOPT_POSTFIELDS] = Funtion::bodyFormat($this->body,
                        $this->head);

        return $this;
    }

    /**
     *
     * @param string $url
     * @return \Dewep\Client\Http
     */
    public function setUrl(string $url): Http
    {
        $this->config[CURLOPT_URL] = $url;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->config[CURLOPT_URL] ?? 'http://localhost';
    }

    /**
     *
     * @param int $timeout
     * @return \Dewep\Client\Http
     */
    public function setTimeout(int $timeout): Http
    {
        $this->config[CURLOPT_TIMEOUT] = $timeout;

        return $this;
    }

    /**
     *
     * @return \Dewep\Client\Http
     */
    public function sslOn(): Http
    {
        $this->config[CURLOPT_SSL_VERIFYHOST] = 2;
        $this->config[CURLOPT_SSL_VERIFYPEER] = true;

        return $this;
    }

    /**
     *
     * @return \Dewep\Client\Http
     */
    public function sslOff(): Http
    {
        $this->config[CURLOPT_SSL_VERIFYHOST] = 0;
        $this->config[CURLOPT_SSL_VERIFYPEER] = false;

        return $this;
    }

    /**
     *
     * @param string $value
     * @return \Dewep\Client\Http
     */
    public function setUserAgent(string $value): Http
    {
        $this->config[CURLOPT_USERAGENT] = $value;

        return $this;
    }

    /**
     *
     * @param string $key
     * @param string $value
     * @return \Dewep\Client\Http
     */
    public function setHead(string $key, string $value): Http
    {
        $key = Funtion::originalHttpKey($key);
        $this->head[$key] = trim($value);

        return $this;
    }

    /**
     *
     * @param string $host
     * @param int $port
     * @param string $login
     * @param string $passwd
     * @return \Dewep\Client\Http
     */
    public function setProxy(string $host, int $port, string $login = null,
            string $passwd = null): Http
    {
        if (!empty($login) && !empty($passwd)) {
            $this->config[CURLOPT_PROXYAUTH] = CURLAUTH_BASIC;
            $this->config[CURLOPT_PROXYUSERPWD] = sprintf("%s:%s", $login,
                    $passwd);
        }

        $this->config[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
        $this->config[CURLOPT_PROXY] = $host;
        $this->config[CURLOPT_PROXYPORT] = $port;

        return $this;
    }

    /**
     *
     * @param string $login
     * @param string $pwd
     * @return \Dewep\Client\Http
     */
    public function setBasicAuth(string $login, string $pwd): Http
    {
        return $this->setHead('Authorization',
                        'Basic ' . base64_encode($login . ':' . $pwd));
    }

    /**
     *
     * @param string $token
     * @return \Dewep\Client\Http
     */
    public function setBearerAuth(string $token): Http
    {
        return $this->setHead('Authorization', 'Bearer ' . $token);
    }

    /**
     *
     * @param array $headers ['Content-Type'=>'application/json']
     * @return array ['Content-Type: application/json']
     */
    protected function heads2line(array $headers): array
    {
        $head = [];
        foreach ($headers as $key => $value) {
            $head[$key] = sprintf('%s: %s', $key, $value);
        }
        $head['Expect'] = 'Expect:';
        return array_values($head);
    }

    /**
     *
     * @param array $headers ['Content-Type: application/json']
     * @return array ['Content-Type'=>'application/json']
     */
    protected function heads2array(array $headers): array
    {
        $head = [];
        foreach ($headers as $item) {
            @list($key, $value) = explode(':', $item, 2);

            $head[trim($key)] = trim($value);
        }
        return $head;
    }

    /**
     *
     * @return \Dewep\Client\Http
     */
    public function make(): Http
    {
        //--
        $this->config[CURLOPT_HTTPHEADER] = $this->heads2line($this->head);
        //--
        $curl = curl_init();
        curl_setopt_array($curl, $this->config ?? []);
        $this->response['body'] = curl_exec($curl);
        $this->response['error'] = curl_error($curl);
        $this->response['info'] = curl_getinfo($curl);
        curl_close($curl);

        //--
        $this->response['http_code'] = $this->response['info']['http_code'] ?? null;
        $this->response['primary_ip'] = $this->response['info']['primary_ip'] ?? null;
        $this->response['request_header'] = $this->response['info']['request_header'] ?? [
                ];

        if (!empty($this->response['http_code'])) {
            @list($this->response['head'], $this->response['body']) = explode("\r\n\r\n",
                    $this->response['body'], 2);
            $this->response['head'] = $this->heads2array(explode("\n",
                            $this->response['head'] ?? ''));
        }

        //--
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getResponseError(): string
    {
        return $this->response['error'];
    }

    /**
     *
     * @return array
     */
    public function getResponseInfo(): array
    {
        return $this->response['info'];
    }

    /**
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return (int) $this->response['http_code'];
    }

    /**
     *
     * @return type
     */
    public function getResponse()
    {
        return $this->response['body'] ?? null;
    }

    /**
     *
     * @param bool $asObject
     * @return type
     */
    public function getResponseJson(bool $asObject = false)
    {
        return json_decode($this->getResponse(), !$asObject);
    }

    /**
     *
     * @param array $namespaces
     * @param array $replace
     * @return \SimpleXMLElement
     */
    public function getResponseXml(array $namespaces = [], array $replace = []): \SimpleXMLElement
    {
        $body = $this->getResponse();
        if (!empty($replace)) {
            foreach ($replace as $from => $to) {
                $body = str_replace($from, $to, $body);
            }
        }

        $backup = libxml_disable_entity_loader(true);
        $backup_errors = libxml_use_internal_errors(true);
        $sxe = simplexml_load_string($body);
        libxml_disable_entity_loader($backup);
        libxml_clear_errors();
        libxml_use_internal_errors($backup_errors);

        if ($sxe === false) {
            return null;
        }

        if (!empty($namespaces)) {
            foreach ($namespaces as $prefix => $ns) {
                $sxe->registerXPathNamespace($prefix, $ns);
            }
        }

        return $sxe;
    }

    /**
     *
     * @return array
     */
    public function getResponseHead(): array
    {
        return $this->response['head'] ?? [];
    }

}
