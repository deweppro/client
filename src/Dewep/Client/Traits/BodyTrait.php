<?php declare(strict_types=1);

namespace Dewep\Client\Traits;

/**
 * Trait BodyTrait
 *
 * @package Dewep\Client\Traits
 */
trait BodyTrait
{
    /** @var array */
    protected static $httpHeaderProtocol = ['HTTP/0', 'HTTP/1', 'HTTP/2',];

    /**
     * @param string $contentType
     * @param mixed  $body
     *
     * @return mixed
     */
    protected function bodyEncode(string $contentType, $body)
    {
        if (!is_array($body)) {
            return $body;
        }

        switch (true) {
            case stripos($contentType, 'json') !== false:
                return (string)json_encode($body);

            case stripos($contentType, 'x-www-form-urlencoded') !== false:
                return http_build_query($body);

            case stripos($contentType, 'xml') !== false:
                $xml = new \SimpleXMLElement('<root/>');
                array_walk_recursive($body, [$xml, 'addChild']);

                return (string)$xml->asXML();
        }

        return $body;
    }

    /**
     * @param string $contentType
     * @param string $body
     *
     * @return mixed
     */
    protected function bodyDecode(string $contentType, string $body)
    {
        switch (true) {
            case stripos($contentType, 'json') !== false:
                return json_decode($body, true);

            case stripos($contentType, 'xml') !== false:

                $backup = libxml_disable_entity_loader(true);
                $backup_errors = libxml_use_internal_errors(true);
                $sxe = simplexml_load_string($body);
                libxml_disable_entity_loader($backup);
                libxml_clear_errors();
                libxml_use_internal_errors($backup_errors);

                return $sxe;
        }

        return $body;
    }

    /**
     * @param string $body
     *
     * @return array
     */
    protected function bodyParse(string $body): array
    {
        $head = '';

        if (strlen($body) === 0) {
            return [$head, $body];
        }

        do {
            $parse = explode("\r\n\r\n", $body, 2);

            $head = (string)($parse[0] ?? '');
            $body = (string)($parse[1] ?? '');

            if (!in_array(
                substr($body, 0, 6),
                self::$httpHeaderProtocol
            )) {
                break;
            }

        } while (true);

        return [$head, $body];
    }
}
