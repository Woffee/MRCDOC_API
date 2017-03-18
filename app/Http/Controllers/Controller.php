<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function success($data = [])
    {
        return response()->json([
            'status_code'   =>   200,
            'message'       =>    'success',
            'data'          =>    $data,
        ]);
    }

    protected function error($message = '')
    {
        return response()->json([
            'status_code'   =>   240,
            'message'       =>    $message,
        ]);
    }
}
