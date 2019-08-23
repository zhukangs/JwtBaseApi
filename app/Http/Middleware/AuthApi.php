<?php

namespace App\Http\Middleware;

use Closure;
use App\Api\Helpers\Api\Jwt;
use App\Api\Helpers\Api\ApiResponse;
use Illuminate\Support\Facades\Redis;

class AuthApi
{
    use JWT, ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('token');

        if (empty($token))
            return $this->failed('token缺失', 403);

        //dd(Redis::ttl($token));
        if(Redis::get($token) != '') return $this->failed('token失效（黑名单）)', 403);

        // 验证token是否有效
        $verify_res = $this->verifyToken($token);

        if (!$verify_res) {
            // 验证token是否在刷新有效期内
            $legal = $this->verifyRefresh($token);
            if (!$legal)
                return $this->failed('登录态失效', 401);

            $user_data = $this->getPayload($token, true);// 获取原token中的数据
            $token     = $this->createAccessToken($user_data);// 重新生成token

            // 设置响应头
            header('token:'.$token);

            return $this->failed('token失效', 403);
        }

        return $next($request);
    }
}
