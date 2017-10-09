<?php
declare(strict_types=1);

namespace Lassonde\Ifpa;

/**
 * Class for making requests to the IFPA api.
 *
 * Consider instead using IfpaClient, which performs additional checks on the
 * results and returns the results as nice objects. If IfpaClient doesn't yet
 * include the api call you're looking for, consider adding it.
 */
class IfpaHttpCaller
{
    const BASE_URL = "https://api.ifpapinball.com/v1/";

    private $_api_key;
    private $_curl_request_factory;
    private $_timeout_seconds;

    public function __construct(
        string $api_key, CurlRequestFactory $curl_request_factory
    ) {
        $this->_api_key = $api_key;
        $this->_curl_request_factory = $curl_request_factory;
        $this->_timeout_seconds = 5;
    }

    /**
     * Sets the request timeout, in seconds.
     */
    public function setTimeoutSeconds(int $timeout_seconds)
    {
        $this->_timeout_seconds = $timeout_seconds;
    }

    /**
     * Makes a get request to the IFPA api.
     *
     * @param $path_suffix Specifies which endpoint to call. The resulting
     *     request path is "https://api.ifpapinball.com/v1/[path_segment]".
     * @param $params String key-value pairs to set on the query string. The
     *     values will automatically be url-encoded.
     *
     * @throws IfpaHttpException if the http status code is anything other than
     *     200.
     *
     * @return decoded json of the result, as an associative array.
     */
    public function get(string $path_suffix, array $params = array()): array
    {
        return $this->_makeRequest($this->_getUrl($path_suffix, $params));
    }

    private function _getUrl(string $path_suffix, array $params = array())
    {
        $builder
            = IfpaHttpCaller::BASE_URL
                . $path_suffix
                . "?api_key=$this->_api_key";
        foreach ($params as $key => $value) {
            $builder .= "&$key=" . rawurlencode($value);
        }
        return $builder;
    }

    private function _makeRequest(string $url): array
    {
        $curlRequest = $this->_curl_request_factory->newCurlRequest($url);
        $curlRequest->setOption(CURLOPT_HEADER, false);
        $curlRequest->setOption(CURLOPT_RETURNTRANSFER, true);
        $curlRequest->setOption(
            CURLOPT_HTTPHEADER,
            array('User-Agent: IfpaClient/1.0')
        );

        $curlRequest
            ->setOption(CURLOPT_CONNECTTIMEOUT, $this->_timeout_seconds);

        $result = $curlRequest->execute();
        $http_status = $curlRequest->getInfo(CURLINFO_HTTP_CODE);
        $curlRequest->close();

        if ($result === false) {
            throw new \RuntimeException('Curl request failed');
        }

        if ($http_status != 200) {
            throw new IfpaHttpException(
                "Non-200 response code", $result, $http_status
            );
        }

        $decoded = json_decode($result, true);
        if ($decoded === null) {
            throw new \UnexpectedValueException(
                "Failed to decode json: $result."
            );
        }
        return $decoded;
    }
}
