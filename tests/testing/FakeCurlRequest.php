<?php
declare(strict_types=1);

namespace Lassonde\Ifpa\Testing;

use Lassonde\Ifpa\CurlRequest;

/**
 * Implementation of CurlRequest for tests.
 */
class FakeCurlRequest implements CurlRequest
{
    private $_result;
    private $_options;
    private $_info;

    public function __construct($result, int $http_code)
    {
        $this->_result = $result;
        $this->_options = array();
        $this->_info = array(CURLINFO_HTTP_CODE => $http_code);
    }

    public function setOption(int $key, $value)
    {
        $this->_options[$key] = $value;
    }

    public function execute()
    {
        return $this->_result;
    }

    public function getInfo(int $key)
    {
        return $this->_info[$key];
    }

    public function close()
    {
    }

    public function getOption(int $key)
    {
        return $this->_options[$key];
    }
}
