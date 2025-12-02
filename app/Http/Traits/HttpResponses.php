<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

trait HttpResponses
{
    /**
     * @param [type] $data
     * @param [type] $message
     * @param [type] $code
     * @return JsonResponse
     */
    protected function success($data, $message = null, $code = ResponseAlias::HTTP_OK, $status = true): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * @param [type] $data
     * @param [type] $message
     * @param [type] $code
     * @return JsonResponse
     */
    protected function error($data, $message = null, $code = ResponseAlias::HTTP_BAD_REQUEST, $status = false): JsonResponse
    {
        $res_data = [
            'status' => $status,
            'message' => $message
        ];
        if($data != null || !empty($data)) {
            $res_data['data'] = $data;
        }

        return response()->json($res_data, $code);
    }


    protected function rollback($data, $message = 'Something went wrong! Process not completed', $code = ResponseAlias::HTTP_BAD_REQUEST, $status = false)
    {
        DB::rollBack();
        return $this->error($data, $message, $code, $status)  ;
    }
}
