<?php declare(strict_types=1);

namespace Dewep\Client;

use Dewep\Client\Traits\BodyTrait;
use Dewep\Client\Traits\UrlTrait;

/**
 * Class Http
 *
 * @package Dewep\Client
 */
class Http implements HttpInterface
{
    use UrlTrait;
    use BodyTrait;

    const HTTP_METHOD_HEAD   = 'HEAD';
    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_DELETE = 'DELETE';

    /** @var resource */
    protected $curl;

    /** @var \Dewep\Client\HttpHeader */
    protected $headers;

    /** @var mixed */
    protected $body;

    /** @var \Dewep\Client\HttpResponse */
    protected $response;

    /** @var array */
    protected $config = [];

    /**
     * Http constructor.
     */
    final public function __construct()
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('cUrl is not found');
        }

        $this->curl = curl_init();

        if ($this->curl === false) {
            throw new \RuntimeException('can`t init cUrl');
        }

        $this->reset();
    }

    /**
     *
     */
    final public function __destruct()
    {
        if ($this->curl !== false) {
            curl_close($this->curl);
        }
    }

    /**
     *
     */
    public function reset()
    {
        $this->config = [
            CURLOPT_URL            => '',
            CURLOPT_USERAGENT      => 'HttpClient/2.0',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_VERBOSE        => true,
            CURLOPT_HEADER         => true,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_CRLF           => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => [],
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_FORBID_REUSE   => true,
        ];

        $this->headers = new HttpHeader();
        $this->body = null;
        $this->response = new HttpResponse();

        return $this;
    }


    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->config[CURLOPT_URL] ?? '';
    }

    /**
     * @param string $url
     * @param array  $query
     *
     * @return \Dewep\Client\Http
     */
    public function setUrl(string $url, array $query): Http
    {
        $this->config[CURLOPT_URL] = $this->queryUpdate($url, $query);

        return $this;
    }

    /**
     * @return \Dewep\Client\HttpHeader
     */
    public function getHeaders(): \Dewep\Client\HttpHeader
    {
        return $this->headers;
    }

    /**
     * @param \Dewep\Client\HttpHeader $headers
     *
     * @return Http
     */
    public function setHeaders(\Dewep\Client\HttpHeader $headers): Http
    {
        $this->headers = $headers;

        return $this;
    }


    /**
     * @param array $data
     *
     * @return \Dewep\Client\Http
     */
    public function get(array $data = []): Http
    {
        $this->config[CURLOPT_CUSTOMREQUEST] = self::HTTP_METHOD_GET;
        $this->body = $data;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return \Dewep\Client\Http
     */
    public function head(array $data = []): Http
    {
        $this->config[CURLOPT_CUSTOMREQUEST] = self::HTTP_METHOD_HEAD;
        $this->body = $data;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return \Dewep\Client\Http
     */
    public function delete(array $data = []): Http
    {
        $this->config[CURLOPT_CUSTOMREQUEST] = self::HTTP_METHOD_DELETE;
        $this->body = $data;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return \Dewep\Client\Http
     */
    public function file(array $data): Http
    {
        $this->body = [];
        $this->headers->setContentType('multipart/form-data');
        $this->config[CURLOPT_CUSTOMREQUEST] = self::HTTP_METHOD_POST;

        foreach ($data as $field => $filepath) {
            if (is_string($filepath) && file_exists($filepath)) {
                $this->body[$field] = new \CURLFile(
                    $filepath,
                    (string)mime_content_type($filepath),
                    basename($filepath)
                );
            }
        }

        return $this;
    }

    /**
     * @param mixed $data
     *
     * @return \Dewep\Client\Http
     */
    public function post($data): Http
    {
        $this->config[CURLOPT_CUSTOMREQUEST] = self::HTTP_METHOD_POST;
        $this->body = $data;

        return $this;
    }

    /**
     * @param mixed $data
     *
     * @return \Dewep\Client\Http
     */
    public function put($data): Http
    {
        $this->config[CURLOPT_CUSTOMREQUEST] = self::HTTP_METHOD_PUT;
        $this->body = $data;

        return $this;
    }

    /**
     * @param int $timeout
     *
     * @return \Dewep\Client\Http
     */
    public function setTimeout(int $timeout = 0): Http
    {
        $this->config[CURLOPT_TIMEOUT] = $timeout;

        return $this;
    }

    /**
     * @param bool $verify
     *
     * @return \Dewep\Client\Http
     */
    public function sslVerify(bool $verify = true): Http
    {
        if ($verify) {
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
     * @return \Dewep\Client\Http
     */
    public function setUserAgent(string $value): Http
    {
        $this->config[CURLOPT_USERAGENT] = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->config[CURLOPT_USERAGENT] ?? '';
    }

    /**
     * @param string      $host
     * @param int         $port
     * @param string|null $login
     * @param string|null $passwd
     *
     * @return \Dewep\Client\Http
     */
    public function setProxy(string $host, int $port, string $login = null, string $passwd = null): Http
    {
        if (!empty($login) && !empty($passwd)) {
            $this->config[CURLOPT_PROXYAUTH] = CURLAUTH_BASIC;
            $this->config[CURLOPT_PROXYUSERPWD] = sprintf(
                "%s:%s",
                $login,
                $passwd
            );
        }

        $this->config[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
        $this->config[CURLOPT_PROXY] = $host;
        $this->config[CURLOPT_PROXYPORT] = $port;

        return $this;
    }

    /**
     * @return \Dewep\Client\Http
     */
    public function exec(): Http
    {
        $this->config[CURLOPT_HTTPHEADER] = $this->headers->all();

        switch ($this->config[CURLOPT_CUSTOMREQUEST] ?? '') {
            case self::HTTP_METHOD_HEAD:
            case self::HTTP_METHOD_GET:
            case self::HTTP_METHOD_DELETE:
                $this->setUrl($this->getUrl(), $this->body);
                break;

            case self::HTTP_METHOD_POST:
            case self::HTTP_METHOD_PUT:
                $this->config[CURLOPT_POSTFIELDS] = $this->bodyEncode(
                    $this->headers->offsetGet('Content-Type') ?? '',
                    $this->body
                );
                break;
        }

        curl_setopt_array($this->curl, $this->config);

        $resp = curl_exec($this->curl);

        $this->response->setup(
            (string)$resp,
            curl_errno($this->curl),
            curl_error($this->curl),
            curl_getinfo($this->curl)
        );

        //--
        return $this;
    }

    /**
     * @return \Dewep\Client\HttpResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

}
