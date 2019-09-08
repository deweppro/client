<?php declare(strict_types=1);

namespace Dewep\Client\Traits;

/**
 * Trait HeaderTrait
 *
 * @package Dewep\Client\Traits
 */
trait HeaderTrait
{
    /**
     * @param string $key
     *
     * @return string
     */
    public function canonizeHeaderKey(string $key): string
    {
        if (stripos($key, 'HTTP_') === 0) {
            $key = substr($key, 5);
        }
        $key = str_replace(['-', '_'], ' ', $key);
        $key = ucwords(strtolower($key));

        return str_replace(' ', '-', trim($key));
    }
}
