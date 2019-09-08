<?php declare(strict_types=1);

namespace Dewep\Client;

use Dewep\Client\Traits\HeaderTrait;

/**
 * Class HttpHeader
 *
 * @package Dewep\Client
 */
class HttpHeader implements \ArrayAccess
{
    use HeaderTrait;

    /**
     * @var array
     */
    protected $header = [];

    /**
     * @param string $data
     *
     * @return \Dewep\Client\HttpHeader
     */
    public function parse(string $data): HttpHeader
    {
        foreach (explode("\n", $data) as $item) {
            $r = explode(': ', $item, 2);
            if (count($r) === 2) {
                $this->offsetSet($r[0], $r[1]);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        $result = ['Expect:'];

        foreach ($this->header as $k => $v) {
            $result[] = $k.': '.$v;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function map(): array
    {
        return $this->header;
    }

    /**
     * @param string $login
     * @param string $pwd
     *
     * @return \Dewep\Client\HttpHeader
     */
    public function setBasicAuth(string $login, string $pwd): HttpHeader
    {
        $this->offsetSet(
            'Authorization',
            sprintf(
                'Basic %s',
                base64_encode(
                    sprintf('%s:%s', $login, $pwd)
                )
            )
        );

        return $this;
    }


    /**
     * @param int $timeout
     *
     * @return \Dewep\Client\HttpHeader
     */
    public function setKeepAlive(int $timeout = 60): HttpHeader
    {
        $this->offsetSet('Connection', 'Keep-Alive');
        $this->offsetSet('Keep-Alive', (string)$timeout);

        return $this;
    }

    /**
     * @param string $type
     *
     * @return \Dewep\Client\HttpHeader
     */
    public function setContentType(string $type): HttpHeader
    {
        $this->offsetSet('Content-Type', $type);

        return $this;
    }

    /**
     * @param string $token
     *
     * @return \Dewep\Client\HttpHeader
     */
    public function setBearerAuth(string $token = ''): HttpHeader
    {
        $this->offsetSet(
            'Authorization',
            sprintf('Bearer %s', $token)
        );

        return $this;
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        $offset = $this->canonizeHeaderKey((string)$offset);

        return array_key_exists($offset, $this->header);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        $offset = $this->canonizeHeaderKey((string)$offset);

        return $this->header[$offset] ?? null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $offset = $this->canonizeHeaderKey((string)$offset);

        switch (true) {
            case is_scalar($value):
                $this->header[$offset] = (string)$value;
                break;

            case is_array($value):
                $this->header[$offset] = (string)implode('; ', $value);
                break;

            case is_object($value) && method_exists($value, '__toString'):
                $this->header[$offset] = (string)$value;
                break;

            case is_object($value) && $value instanceof \JsonSerializable:
                $this->header[$offset] = (string)json_encode($value);
                break;

            default:
                return;
        }

        ksort($this->header);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $offset = $this->canonizeHeaderKey((string)$offset);
        unset($this->header[$offset]);
    }

}
