<?php

namespace App\RedisResponse\Output;

use Illuminate\Support\Facades\Redis;
use App\RedisResponse\BaseClassForResponse;

class Output
{
    public function metadata($row, $responseType)
    {
        return [
            'API Version'           => '1.0.1',
            'Response Time'         => date('Y-m-d H:i:s'),
            'Data Response Type'    => $responseType,
            'Api Url'               => BaseClassForResponse::route()->apiRoute,
            'Row Count'             => $row,
            'Content Type'          => 'application/json',
            'Website'               => config('app.APP_URL')
        ];
    }
}
