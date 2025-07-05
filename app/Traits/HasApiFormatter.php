<?php

namespace App\Traits;
//Modelo de transformacion de datos 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

trait HasApiFormatter 
{
    protected function successResponse($data, $message = null, $code =200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message, $code)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => null
        ], $code);
    }
}
