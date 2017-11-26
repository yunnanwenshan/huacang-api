<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        // ModelNotFoundException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $e
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $e
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
//        $Origin = !is_null($request->header('Origin')) ? $request->header('Origin'):'';
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers:Authorization, X_Requested_With, x-requested-with, Server-Token, Origin, Content-Type, Cookie, Accept');
        header('Access-Control-Allow-Methods :GET, POST, PATCH, PUT, OPTIONS');
        header('Access-Control-Allow-Credentials:false');
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        $jsonp_callback = $request->input('callback');

        if ($e instanceof HttpException) {
            $response = response()->json([
                'code' => intval($e->getStatusCode(), 10),
                'msg'  => $e->getMessage(),
            ], 200, [], JSON_UNESCAPED_UNICODE);

            if (!empty($jsonp_callback)) {
                return $response->setCallback($jsonp_callback);
            } else {
                return $response;
            }
        } else {
            $response =  response()->json([
                'code' => 500,
                'msg'  => $e->getMessage(),
            ], 200, [], JSON_UNESCAPED_UNICODE);

            if (!empty($jsonp_callback)) {
                return $response->setCallback($jsonp_callback);
            } else {
                return $response;
            }
        }
    }
}
