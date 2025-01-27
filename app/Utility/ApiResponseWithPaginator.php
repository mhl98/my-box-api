<?php

namespace App\Utility;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiResponseWithPaginator extends JsonResponse
{
    /**
     * @param $data
     * @param $pageData
     * @param int $apiStatus
     * @param int $httpStatus
     * @param array $headers
     * @param int $options
     */
    public function __construct(
        $data,
        $pageData = null,
        $more = null,
        $apiStatus = 200,
        $httpStatus = 200,
        $headers = [],
        $options = 0
    )
    {
        $response = [];
        $response['status'] = $apiStatus;
        if ($data instanceof ResourceCollection) {
            $response['data'] = $data;
        } else {
            $response['data'] = $data->items();
        }

        $response['page-data'] = $pageData;

        $response['more'] = $more;

        $response['pagination'] = [
            'total' => $data->total(),
            'per_page' => $data->perPage(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'next_page_url' => $data->nextPageUrl(),
            'previous_page_url' => $data->previousPageUrl(),

        ];
        parent::__construct($response, $httpStatus, $headers, $options);
    }
}
