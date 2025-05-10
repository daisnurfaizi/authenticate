<?php

namespace Ijp\Auth\Helper;

class ResponseJsonFormater
{
    /**
     * @var array
     */
    protected static $response = [
        'status' => 'success',
        'message' => null,

    ];

    /**
     * @param null $data
     * @param null $message
     */
    public static function success($data = null, $message = null, $code = 200)
    {

        if ($data) {
            self::$response['data'] = $data;
        }
        self::$response['message'] = $message;

        return response()->json(self::$response, $code);
    }

    public static function error($message = null, $code = 400, $status = 'error', $data = null)
    {
        if ($data) {
            self::$response['data'] = $data;
        }

        self::$response['status'] = $status;
        self::$response['message'] = $message;

        return response()->json(self::$response, $code);
    }
}
