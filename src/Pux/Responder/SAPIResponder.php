<?php
namespace Pux\Responder;
use RuntimeException;
use LogicException;

class SAPIResponder
{
    /**
     * @var Resource currently not used.
     */
    protected $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function respondWithString($response)
    {
        http_response_code(200);
        fwrite($this->resource, $response);
    }

    public function respond($response)
    {
        // treat string format response as 200 OK
        if (is_string($response)) {
            // http_response_code is only available after 5.4
            http_response_code(200);
            fwrite($this->resource, $response);
        } else if (is_array($response)) {
            list($code, $headers, $body) = $response;
            http_response_code($code);
            foreach ($headers as $header) {
                if (is_string($header)) {
                    @header($header);
                } else if (is_array($header)) {
                    // support for [ 'Content-Type' => 'text/html' ]
                    foreach ($header as $field => $fieldValue) {
                        // TODO: escape field value correctly
                        @header($field . ':' . $fieldValue);
                    }
                } else {
                    throw new RuntimeException('Unexpected header value type.');
                }
            }
            fwrite($this->resource, $body);
        } else {
            throw new LogicException("Unsupported response value type.");
        }
    }
}
