<?php
declare(strict_types=1);

namespace Lassonde\Ifpa;

/**
 * Exception representing unexpected data returned by the IFPA api.
 */
class IfpaDataException extends \RuntimeException
{
    private $_data;

    /**
     * @param $data The offending IFPA response, as an associative array.
     */
    public function __construct(
        $message, array $data, Exception $previous = null
    ) {
        $this->_data = $data;
        parent::__construct($message, 0, $previous);
    }

    public function __toString()
    {
        $encodedData = json_encode($this->_data);
        return __CLASS__ . ": {$this->message}: $encodedData}\n";
    }

    /**
     * @return the offending IFPA response.
     */
    public function getData(): array
    {
        return $this->_data;
    }
}
