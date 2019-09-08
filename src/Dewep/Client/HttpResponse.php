<?php declare(strict_types=1);

namespace Dewep\Client;

use Dewep\Client\Traits\BodyTrait;

/**
 * Class HttpResponse
 *
 * @package Dewep\Client
 */
class HttpResponse
{
    use BodyTrait;

    /** @var \Dewep\Client\HttpHeader */
    protected $headers;

    /** @var array */
    protected $info = [];

    /** @var mixed */
    protected $body;

    /** @var int */
    protected $errno = 0;

    /** @var string */
    protected $error = '';

    /**
     * HttpResponse constructor.
     */
    public function __construct()
    {
        $this->headers = new HttpHeader();
    }

    /**
     * @param string $raw
     * @param int    $errno
     * @param string $error
     * @param array  $info
     */
    public function setup(string $raw, int $errno, string $error, array $info)
    {
        list($h, $this->body) = $this->bodyParse($raw);

        $this->headers->parse($h);

        $this->error = $error;
        $this->errno = $errno;
        $this->info = $info;
    }

    /**
     * @return array
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * @return int
     */
    public function getErrno(): int
    {
        return $this->errno;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return (int)$this->info['http_code'];
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return (string)$this->info['content_type'];
    }

    /**
     * @param bool $raw
     *
     * @return mixed|\SimpleXMLElement|string
     */
    public function getBody(bool $raw = false)
    {
        if ($raw) {
            return $this->body;
        }

        return $this->bodyDecode((string)$this->info['content_type'], $this->body);
    }

    /**
     * @param array $namespaces
     * @param array $replace
     *
     * @return \SimpleXMLElement
     */
    public function asXml(array $namespaces = [], array $replace = []): \SimpleXMLElement
    {
        $body = $this->body;

        if (!empty($replace)) {
            foreach ($replace as $from => $to) {
                $body = str_replace($from, $to, $body);
            }
        }

        $sxe = $this->bodyDecode('xml', $body);

        if (!$sxe instanceof \SimpleXMLElement) {
            throw new \LogicException('XML format not recognized.');
        }

        if (!empty($namespaces)) {
            foreach ($namespaces as $prefix => $ns) {
                $sxe->registerXPathNamespace($prefix, $ns);
            }
        }

        return $sxe;
    }
}
