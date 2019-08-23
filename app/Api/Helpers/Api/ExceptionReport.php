<?php
/**
 * Created by PhpStorm.
 * User: zhukang
 * Date: 2019/8/22
 * Time: 14:10
 */
namespace App\Api\Helpers\Api;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ExceptionReport
{
    use ApiResponse;

    /**
     * @var Exception
     */
    public $exception;
    /**
     * @var Request
     */
    public $request;

    /**
     * @var
     */
    protected $report;

    /**
     * ExceptionReport constructor.
     * @param Request $request
     * @param Exception $exception
     */
    function __construct(Request $request, Exception $exception)
    {
        $this->request = $request;
        $this->exception = $exception;
    }

    /**
     * @var array
     */
    public $doReport = [
        AuthenticationException::class => ['未授权',401],
        ModelNotFoundException::class => ['改模型未找到',404],
        AuthorizationException::class => ['没有此权限',403],
        ValidationException::class => [],

    ];

    public function register($className,callable $callback){

        $this->doReport[$className] = $callback;
    }

    /**
     * @return bool
     */
    public function shouldReturn(){

        if (! ($this->request->wantsJson() || $this->request->ajax())){

            return false;
        }

        foreach (array_keys($this->doReport) as $report){

            if ($this->exception instanceof $report){

                $this->report = $report;
                return true;
            }
        }

        return false;

    }

    /**
     * @param Exception $e
     * @return static
     */
    public static function make(Exception $e){

        return new static(\request(),$e);
    }

    /**
     * @return mixed
     */
    public function report(){

        if ($this->exception instanceof ValidationException){

            $error = array_first($this->exception->errors());

            return $this->failed(array_first($error),$this->exception->status);

        }

        $message = $this->doReport[$this->report];

        return $this->failed($message[0],$message[1]);

    }

}