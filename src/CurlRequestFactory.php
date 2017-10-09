<?php
declare(strict_types=1);

namespace Lassonde\Ifpa;

/**
 * Class to generate CurlRequests.
 *
 * This class exists for the purpose of using alternate implementations of
 * CurlRequest in tests.
 */
class CurlRequestFactory
{
    public function newCurlRequest(string $url): CurlRequest
    {
        return new CurlRequestImpl($url);
    }
}
