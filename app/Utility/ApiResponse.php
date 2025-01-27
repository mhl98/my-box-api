<?php

namespace App\Utility;

use Illuminate\Http\JsonResponse;

class ApiResponse extends JsonResponse
{
    /**
     * ApiResponse constructor.
     * @param mixed $data
     * @param int $apiStatus
     * @param string|null $message
     * @param int $httpStatus
     * @param array $headers
     * @param int $options
     */
    public function __construct(
        $data = null,
        $apiStatus = 200,
        $message = null,
        $httpStatus = 200,
        $headers = [],
        $options = 0
    ) {
        $response = [
            'status' => $apiStatus,
            'message' => $message ?? ($apiStatus === 200 ? 'Success' : 'Error'),
            'data' => $data,
        ];

        parent::__construct($response, $httpStatus, $headers, $options);
    }

    /**
     * Static method for success responses.
     */
    public static function success($data = null, string $message = 'Success', int $httpStatus = 200): self
    {
        return new self($data, 200, $message, $httpStatus);
    }

    /**
     * Static method for error responses.
     */
    public static function error(string $message, int $httpStatus = 400, $data = null): self
    {
        return new self($data, $httpStatus, $message, $httpStatus);
    }
}
