<?php
/**
 * Created by PhpStorm.
 * User: zhukang
 * Date: 2019/8/22
 * Time: 11:53
 */

namespace App\Http\Controllers\Api;

use App\Api\Helpers\Api\ApiResponse;
use App\Api\Helpers\Api\Jwt;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{

    use Jwt,ApiResponse;

    // 其他通用的Api帮助函数

}