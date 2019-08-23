<?php

namespace App\Exceptions;

use App\Api\Helpers\Api\ExceptionReport;
use App\Api\Helpers\Api\ApiResponse;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    use ApiResponse;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if (Config::get('app.debug') === false) {
            if ($request->ajax()) {
                $message = $exception->getMessage();
                $line    = $exception->getLine();
                $file    = $exception->getFile();
                $code    = $exception->getCode();
                return response()->json(['code' => 500, 'msg' => '请求发生错误!', 'data' => [
                    'code'    => $code,
                    'line'    => $line,
                    'file'    => $file,
                    'message' => $message,
                ]]);
            } else {
                return response()->view('base.404');
            }
        }

        //对api的request返回数据格式化
        if($request->is('api/*')){
            //表单验证
            if($exception instanceof ValidationException){
                $error = array_first($exception->errors());
                //return $this->message(array_first($error),$exception->status);
                return $this->message(array_first($error),'error');
            }
        }

        // 将方法拦截到自己的ExceptionReport
        $reporter = ExceptionReport::make($exception);

        if ($reporter->shouldReturn()){
            return $reporter->report();
        }

        return parent::render($request, $exception);
    }
}
