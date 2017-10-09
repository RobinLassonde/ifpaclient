<?php
declare(strict_types=1);

namespace Lassonde\Ifpa;

/**
 * Standard implementation of CurlRequest.
 */
class CurlRequestImpl implements CurlRequest
{
    private $_handle = null;

    public function __construct(string $url)
    {
        $this->_handle = curl_init($url);
    }

    public function setOption(int $key, $value)
    {
        curl_setopt($this->_handle, $key, $value);
    }

    public function execute()
    {
        return curl_exec($this->_handle);
    }

    public function getInfo(int $key)
    {
        return curl_getinfo($this->_handle, $key);
    }

    public function close(): void
    {
        curl_close($this->_handle);
    }
}
