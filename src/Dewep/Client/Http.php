<?php

namespace Dewep\Client;

use Dewep\Funtion;

/**
 * Class Http
 *
 * @package Dewep\Client
 */
class Http
{
    const HTTP_METHOD_HEAD = 'HEAD';
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_PUT = 'PUT';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_DELETE = 'DELETE';

    /** @var mixed */
    protected $logger;
    /** @var array */
    protected $head = [];
    /** @var array */
    protected $config = [
        CURLOPT_URL => 'http://localhost',
        CURLOPT_USERAGENT => 'HttpClient/1.0',
        CURLOPT_RETURNTRANSFER => true,
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
    ];
    /** @var string */
    protected $body;
    /** @var array */
    protected $response = [];

    /**
     * @param mixed $logger
     */
    public function __construct($logger = null)
    {
        $this->logger = $logger;
    }


    /**
     * @return string
     */
    public function getUrl(): string
    {
        return empty($this->config[CURLOPT_URL]) ? 'http://localhost' : $this->config[CURLOPT_URL];
    }

    /**
     * @param string $url
     *
     * @return Http
     */
    public function setUrl(string $url = 'http://localhost'): Http
    {
        $this->config[CURLOPT_URL] = $url;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return Http
     */
    public function get(array $data = []): Http
    {
        $url = Funtion::updateUrl($this->getUrl(), ['query' => $data]);

        $this->config[CURLOPT_CUSTOMREQUEST] = self::HTTP_METHOD_GET;

        return $this->setUrl($url);
    }


    /**
     * @param array $data
     *
     * @return Http
     */
    public function head(array $data = []): Http
    {
        $url = Funtion::updateUrl($this->getUrl(), ['query' => $data]);

        $this->config[CURLOPT_CUSTOMREQUEST] = self::HTTP_METHOD_HEAD;

        return $this->setUrl($url);
    }

    /**
     * @param array $data
     *
     * @return Http
     */
    public function delete(array $data = []): Http
    {
        $url = Funtion::updateUrl($this->getUrl(), ['query' => $data]);

        $this->config[CURLOPT_CUSTOMREQUEST] = self::HTTP_METHOD_DELETE;

        return $this->setUrl($url);
    }

    /**
     * @param mixed $body
     *
     * @return Http
     */
    public function post($body): Http
    {
        $this->body = $body;

        $this->config[CURLOPT_CUSTOMREQUEST] = self::HTTP_METHOD_POST;
        $this->config[CURLOPT_POSTFIELDS] = Funtion::bodyFormat($this->body, $this->head);

        return $this;
    }

    /**
     * @param mixed $body
     *
     * @return Http
     */
    public function put($body): Http
    {
        $this->body = $body;

        $this->config[CURLOPT_CUSTOMREQUEST] = self::HTTP_METHOD_PUT;
        $this->config[CURLOPT_POSTFIELDS] = Funtion::bodyFormat($this->body, $this->head);

        return $this;
    }

    /**
     * @param int $timeout
     *
     * @return Http
     */
    public function setTimeout(int $timeout = 0): Http
    {
        $this->config[CURLOPT_TIMEOUT] = $timeout;

        return $this;
    }

    /**
     * @param bool $status
     *
     * @return $this
     */
    public function checkSSL(bool $status = true): Http
    {
        if ($status) {
            $this->config[CURLOPT_SSL_VERIFYHOST] = 2;
            $this->config[CURLOPT_SSL_VERIFYPEER] = true;
        } else {
            $this->config[CURLOPT_SSL_VERIFYHOST] = 0;
            $this->config[CURLOPT_SSL_VERIFYPEER] = false;
        }


        return $this;
    }

    /**
     * @param string $value
     *
     * @return Http
     */
    public function setUserAgent(string $value = 'HttpClient/1.0'): Http
    {
        $this->config[CURLOPT_USERAGENT] = $value;

        return $this;
    }

    /**
     * @param string $host
     * @param int    $port
     * @param string $login
     * @param string $passwd
     *
     * @return Http
     */
    public function setProxy(
        string $host = '127.0.0.1',
        int $port = 3128,
        string $login = '',
        string $passwd = ''
    ): Http {
        if (!empty($login) && !empty($passwd)) {
            $this->config[CURLOPT_PROXYAUTH] = CURLAUTH_BASIC;
            $this->config[CURLOPT_PROXYUSERPWD] = sprintf("%s:%s", $login, $passwd);
        }

        $this->config[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
        $this->config[CURLOPT_PROXY] = $host;
        $this->config[CURLOPT_PROXYPORT] = $port;

        return $this;
    }

    /**
     * @param string $login
     * @param string $pwd
     *
     * @return Http
     */
    public function setBasicAuth(string $login = '', string $pwd = ''): Http
    {
        return $this->setHead(
            'Authorization',
            sprintf(
                'Base %s',
                base64_encode(
                    sprintf('%s:%s', $login, $pwd)
                )
            )
        );
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return Http
     */
    public function setHead(string $key = '', string $value = ''): Http
    {
        $key = Funtion::originalHttpKey($key);
        $this->head[$key] = trim($value);

        return $this;
    }

    /**
     * @param string $token
     *
     * @return Http
     */
    public function setBearerAuth(string $token = ''): Http
    {
        return $this->setHead(
            'Authorization',
            sprintf('Bearer %s', $token)
        );
    }

    /**
     * @return Http
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
        $this->response['http_code'] = (int)($this->response['info']['http_code'] ?? 0);
        $this->response['primary_ip'] = (string)($this->response['info']['primary_ip'] ?? '');
        $this->response['request_header'] = $this->response['info']['request_header'] ?? [];

        if ($this->getStatusCode() > 0) {

            do {
                $parse = false;

                @list($this->response['head'], $this->response['body']) = explode(
                    "\r\n\r\n",
                    $this->response['body'],
                    2
                );

                if (in_array(
                    substr($this->response['body'], 0, 6),
                    ['HTTP/0', 'HTTP/1', 'HTTP/2',]
                )) {
                    $parse = true;
                }


            } while ($parse);

            $this->response['head'] = $this->heads2array(
                explode(
                    "\n",
                    $this->response['head'] ?? ''
                )
            );
        }

        //--
        return $this;
    }

    /**
     * @param array $headers
     *
     * @return array
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
     * @param array $headers
     *
     * @return array
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
     * @return array
     */
    public function getResponseError(): array
    {
        return $this->response['error'];
    }

    /**
     * @return array
     */
    public function getResponseInfo(): array
    {
        return $this->response['info'];
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return (int)$this->response['http_code'];
    }

    /**
     * @param bool $asObject
     *
     * @return mixed
     */
    public function getResponseJson(bool $asObject = false)
    {
        return json_decode($this->getResponse(), !$asObject);
    }

    /**
     * @return mixed|null
     */
    public function getResponse()
    {
        return $this->response['body'] ?? null;
    }

    /**
     * @param array $namespaces
     * @param array $replace
     *
     * @return \SimpleXMLElement|null
     */
    public function getResponseXml(array $namespaces = [], array $replace = [])
    {
        $body = $this->getResponse();
        if ($body === null || !is_string($body)) {
            return null;
        }

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
     * @return array
     */
    public function getResponseHead(): array
    {
        return $this->response['head'] ?? [];
    }

    /**
     * @return string
     */
    public function getServerIp(): string
    {
        return $this->response['primary_ip'] ?? '';
    }
}
