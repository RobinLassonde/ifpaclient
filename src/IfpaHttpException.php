<?php
declare(strict_types=1);

namespace Lassonde\Ifpa;

/**
 * Exception representing undesired http status code returned by the IFPA api.
 *
 * The exception code will be set to the http status code.
 */
class IfpaHttpException extends \RuntimeException
{
    private $_response_body;

    public function __construct(
        $message, string $response_body, $code, Exception $previous = null
    ) {
        $this->_response_body = $response_body;
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__
            . ": [{$this->code}]: {$this->message}: {$this->_response_body}\n";
    }

    public function getResponseBody(): string
    {
        return $this->_response_body;
    }
}
