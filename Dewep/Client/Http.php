<?php

namespace Dewep\Client;

use Dewep\Funtion;

class Http
{

    const HTTP_METHOD_HEAD   = 'HEAD';
    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_DELETE = 'DELETE';

    /** @var mixed */
    protected $logger;
    /** @var array */
    protected $head = [];
    /** @var array */
    protected $config = [
        CURLOPT_URL            => 'http://localhost',
        CURLOPT_USERAGENT      => 'HttpClient/1.0',
        CURLOPT_RETURNTRANSFER => true,
        CURLINFO_HEADER_OUT    => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_VERBOSE        => true,
        CURLOPT_HEADER         => true,
        CURLINFO_HEADER_OUT    => true,
        CURLOPT_CRLF           => true,
        CURLOPT_FRESH_CONNECT  => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => [],
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
    ];
    /** @var string */
    protected $body;
    /** @var array */
    protected $response = [];

    /**
     * @param null $logger
     */
    public function __construct($logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $data
     * @return Http
     */
    public function methodGet($data = [])
    {
        $url = Funtion::updateUrl($this->getUrl(), ['query' => is_array($data) ? $data : [$data]]);

        $this->config[CURLOPT_CUSTOMREQUEST] = static::HTTP_METHOD_GET;

        return $this->setUrl($url);
    }

    /**
     * @return mixed|string
     */
    public function getUrl()
    {
        return empty($this->config[CURLOPT_URL]) ? 'http://localhost' : $this->config[CURLOPT_URL];
    }

    /**
     * @param string $url
     * @return Http
     */
    public function setUrl($url = 'http://localhost')
    {
        $this->config[CURLOPT_URL] = (string)$url;

        return $this;
    }

    /**
     * @param array $data
     * @return Http
     */
    public function methodHead($data = [])
    {
        $url = Funtion::updateUrl(
            $this->getUrl(),
            ['query' => is_array($data) ? $data : [(string)$data]]
        );

        $this->config[CURLOPT_CUSTOMREQUEST] = static::HTTP_METHOD_HEAD;

        return $this->setUrl($url);
    }

    /**
     * @param array $data
     * @return Http
     */
    public function methodDelete($data = [])
    {
        $url = Funtion::updateUrl(
            $this->getUrl(),
            ['query' => is_array($data) ? $data : [(string)$data]]
        );

        $this->config[CURLOPT_CUSTOMREQUEST] = static::HTTP_METHOD_DELETE;

        return $this->setUrl($url);
    }

    /**
     * @param $body
     * @return Http
     */
    public function methodPost($body)
    {
        $this->body = $body;

        $this->config[CURLOPT_CUSTOMREQUEST] = static::HTTP_METHOD_POST;
        $this->config[CURLOPT_POSTFIELDS]    = Funtion::bodyFormat($this->body, $this->head);

        return $this;
    }

    /**
     * @param $body
     * @return Http
     */
    public function methodPut($body)
    {
        $this->body = $body;

        $this->config[CURLOPT_CUSTOMREQUEST] = static::HTTP_METHOD_PUT;
        $this->config[CURLOPT_POSTFIELDS]    = Funtion::bodyFormat($this->body, $this->head);

        return $this;
    }

    /**
     * @param int $timeout
     * @return Http
     */
    public function setTimeout($timeout = 0)
    {
        $this->config[CURLOPT_TIMEOUT] = (int)$timeout;

        return $this;
    }

    /**
     * @return Http
     */
    public function sslOn()
    {
        $this->config[CURLOPT_SSL_VERIFYHOST] = 2;
        $this->config[CURLOPT_SSL_VERIFYPEER] = true;

        return $this;
    }

    /**
     * @return Http
     */
    public function sslOff()
    {
        $this->config[CURLOPT_SSL_VERIFYHOST] = 0;
        $this->config[CURLOPT_SSL_VERIFYPEER] = false;

        return $this;
    }

    /**
     * @param string $value
     * @return Http
     */
    public function setUserAgent($value = 'HttpClient/1')
    {
        $this->config[CURLOPT_USERAGENT] = (string)$value;

        return $this;
    }

    /**
     * @param string $host
     * @param int $port
     * @param string $login
     * @param string $passwd
     * @return Http
     */
    public function setProxy($host = '127.0.0.1', $port = 3128, $login = '', $passwd = '')
    {
        if (!empty($login) && !empty($passwd)) {
            $this->config[CURLOPT_PROXYAUTH]    = CURLAUTH_BASIC;
            $this->config[CURLOPT_PROXYUSERPWD] = sprintf("%s:%s", (string)$login, (string)$passwd);
        }

        $this->config[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
        $this->config[CURLOPT_PROXY]     = (string)$host;
        $this->config[CURLOPT_PROXYPORT] = (int)$port;

        return $this;
    }

    /**
     * @param string $login
     * @param string $pwd
     * @return Http
     */
    public function setBasicAuth($login = '', $pwd = '')
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
     * @return Http
     */
    public function setHead($key = '', $value = '')
    {
        $key              = (string)Funtion::originalHttpKey((string)$key);
        $this->head[$key] = trim((string)$value);

        return $this;
    }

    /**
     * @param string $token
     * @return Http
     */
    public function setBearerAuth($token = '')
    {
        return $this->setHead(
            'Authorization',
            sprintf('Bearer %s', $token)
        );
    }

    /**
     * @return Http
     */
    public function make()
    {
        //--
        $this->config[CURLOPT_HTTPHEADER] = $this->heads2line($this->head);
        //--
        $curl = curl_init();
        curl_setopt_array($curl, empty($this->config) ? [] : $this->config);
        $this->response['body']  = curl_exec($curl);
        $this->response['error'] = curl_error($curl);
        $this->response['info']  = curl_getinfo($curl);
        curl_close($curl);

        //--
        $this->response['http_code']      = empty($this->response['info']['http_code']) ?
            null : (int)$this->response['info']['http_code'];
        $this->response['primary_ip']     = empty($this->response['info']['primary_ip']) ?
            null : (string)$this->response['info']['primary_ip'];
        $this->response['request_header'] = empty($this->response['info']['request_header']) ?
            [] : $this->response['info']['request_header'];

        if (!empty($this->response['http_code'])) {

            $parse = true;
            do {
                @list($this->response['head'], $this->response['body']) = explode(
                    "\r\n\r\n",
                    $this->response['body'],
                    2
                );

                if (in_array(
                    substr($this->response['body'], 0, 6),
                    ['HTTP/0', 'HTTP/1', 'HTTP/2',]
                )) {
                    $parse = false;
                }


            } while ($parse);

            $this->response['head'] = $this->heads2array(
                explode(
                    "\n",
                    empty($this->response['head']) ? '' : $this->response['head']
                )
            );
        }

        //--
        return $this;
    }

    /**
     * @param array $headers ['Content-Type'=>'application/json']
     * @return array ['Content-Type: application/json']
     */
    protected function heads2line($headers)
    {
        $headers = is_array($headers) ? $headers : [$headers];
        $head    = [];
        foreach ($headers as $key => $value) {
            $head[$key] = sprintf('%s: %s', $key, $value);
        }
        $head['Expect'] = 'Expect:';

        return array_values($head);
    }

    /**
     * @param array $headers ['Content-Type: application/json']
     * @return array ['Content-Type'=>'application/json']
     */
    protected function heads2array($headers)
    {
        $headers = is_array($headers) ? $headers : [$headers];
        $head    = [];
        foreach ($headers as $item) {
            @list($key, $value) = explode(':', $item, 2);

            $head[trim($key)] = trim($value);
        }

        return $head;
    }

    /**
     * @return mixed
     */
    public function getResponseError()
    {
        return $this->response['error'];
    }

    /**
     * @return mixed
     */
    public function getResponseInfo()
    {
        return $this->response['info'];
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return (int)$this->response['http_code'];
    }

    /**
     * @param bool $asObject
     * @return mixed
     */
    public function getResponseJson($asObject = false)
    {
        return json_decode($this->getResponse(), !$asObject);
    }

    /**
     * @return mixed|null
     */
    public function getResponse()
    {
        return empty($this->response['body']) ? null : $this->response['body'];
    }

    /**
     * @param array $namespaces
     * @param array $replace
     * @return \SimpleXMLElement
     */
    public function getResponseXml($namespaces = [], $replace = [])
    {
        $body = $this->getResponse();
        if (!empty($replace)) {
            $replace = is_array($replace) ? $replace : [$replace];
            foreach ($replace as $from => $to) {
                $body = str_replace($from, $to, $body);
            }
        }

        $backup        = libxml_disable_entity_loader(true);
        $backup_errors = libxml_use_internal_errors(true);
        $sxe           = simplexml_load_string($body);
        libxml_disable_entity_loader($backup);
        libxml_clear_errors();
        libxml_use_internal_errors($backup_errors);

        if ($sxe === false) {
            return null;
        }

        if (!empty($namespaces)) {
            $namespaces = is_array($namespaces) ? $namespaces : [$namespaces];
            foreach ($namespaces as $prefix => $ns) {
                $sxe->registerXPathNamespace($prefix, $ns);
            }
        }

        return $sxe;
    }

    /**
     * @return array|mixed
     */
    public function getResponseHead()
    {
        return empty($this->response['head']) ? [] : $this->response['head'];
    }

    /**
     * @return mixed|null
     */
    public function getServerIp()
    {
        return empty($this->response['primary_ip']) ? null : $this->response['primary_ip'];
    }
}
