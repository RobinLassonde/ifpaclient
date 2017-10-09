<?php
declare(strict_types=1);

namespace Lassonde\Ifpa;

/**
 * Wrapper interface for making a curl request.
 */
interface CurlRequest
{
    /**
     * @see curl_setopt
     */
    public function setOption(int $key, $value);

    /**
     * @see curl_exec
     */
    public function execute();

    /**
     * @see curl_getinfo
     */
    public function getInfo(int $key);

    /**
     * @see curl_close
     */
    public function close();
}
