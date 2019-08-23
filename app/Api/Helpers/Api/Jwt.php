<?php
/**
 * Created by PhpStorm.
 * User: zhukang
 * Date: 2019/8/22
 * Time: 16:39
 */
namespace App\Api\Helpers\Api;

use Illuminate\Support\Facades\Redis;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

/**
 * Json Web Token, 活跃状态下无限期使用
 */
trait Jwt{
    private static $key = 'zkapi';// 使用HMAC生成信息摘要时所使用的密钥
    private static $access_exp  = 900;// access token过期秒数(默认15分钟)
    private static $refresh_exp = 7200;// refresh token过期秒数(默认2小时)

    /**
     * 生成access_token
     * @param array   $data  自定义字段
     * @return string
     */
    public static function createAccessToken(array $data=[])
    {
        $token = self::getToken($data);
        return $token;
    }

    /**
     * 生成refresh_token
     * @param array   $data  自定义字段
     * @return string
     */
    public static function createRefreshToken(array $data=[])
    {
        $token = self::getToken($data);
        return $token;
    }

    /**
     * 获取jwt token
     * @param int   $exp_time token过期时间
     * @param array $data     自定义数据
     * @return string
     */
    public static function getToken(array $data=[])
    {
        $builder = new Builder();
        $signer  = new Sha256();

        $token_id  = md5(uniqid('JWT').time());
        $curr_time = time();
        $exp       = bcadd($curr_time, self::$access_exp);
        $ref_exp   = bcadd($curr_time, self::$refresh_exp);

        // 官方字段可选用
        $builder->setIssuer('admin');// 设置iss发行人
        $builder->setAudience('user');// 设置aud接收人
        $builder->setId($token_id, true);// 设置jti 该Token唯一标识
        $builder->setIssuedAt($curr_time);// 设置iat 生成token的时间
        $builder->setNotBefore($curr_time);// 设置nbf token生效时间
        $builder->setExpiration($exp);// 设置exp 过期时间

        // 设置刷新有效期
        $builder->set('ref_exp', $ref_exp);

        // 自定义设定
         /*if ( !empty($setting) ) {
             foreach ($setting as $key => $value) {
                 $builder->set($key, $value);
             }
         }*/

        // 自定义数据
        if ( !empty($data) ) {
            $builder->set('data', $data);
        }

        $builder->sign($signer, self::$key);// 对上面的信息使用sha256算法签名
        $token = $builder->getToken();// 获取生成的token
        $token = (string) $token;

        return $token;
    }

    /**
     * 获取token中的Payload字段
     * @param string $token        token
     * @param string $only_data    只返回自定义字段(默认只返回所有字段)
     * @return bool|array
     */
    public static function getPayload(string $token, $only_data=false)
    {
        if ( !$token ) {
            return false;
        }

        $tokens = explode('.', $token);
        if (count($tokens) != 3) {
            return false;
        }

        list($base64header, $base64payload, $sign) = $tokens;

        $payload = json_decode(base64_decode($base64payload));

        $data = [];
        if ( $only_data ) {
            if ( isset($payload->data) ) {
                if ( is_string($payload->data) ) {
                    $data = json_decode($payload->data, JSON_OBJECT_AS_ARRAY);
                }else{
                    $data = (array) $payload->data;
                }
            }
        }else{
            $data = (array) $payload;
        }

        return $data;
    }

    /**
     * 验证token是否有效,默认验证exp,iat时间
     * @param string $token 需要验证的token
     * @return bool
     */
    public static function verifyToken($token)
    {
        // 验证签名是否合法
        $legal = self::verifySign($token);
        if ( !$legal ) {
            return false;
        }

        $payload = self::getPayload($token);
        if ( !$payload ) {
            return false;
        }

        // 签发时间大于当前服务器时间验证失败
        if (isset($payload['iat']) && $payload['iat'] > time()) {
            return false;
        }

        // 生效时间大于当前服务器时间验证失败
        if (isset($payload['nbf']) && $payload['nbf'] > time()) {
            return false;
        }

        // 当前服务器时间大于过期时间验证失败
        if (isset($payload['exp']) && time() > $payload['exp']) {
            return false;
        }

        return true;
    }

    /**
     * 验证token是否在刷新有效期内
     * @param string $token 需要验证的token
     * @return bool
     */
    public static function verifyRefresh($token)
    {
        // 验证签名是否合法
        $legal = self::verifySign($token);
        if ( !$legal ) {
            return false;
        }

        $payload = self::getPayload($token);
        if ( !$payload ) {
            return false;
        }

        // 签发时间大于当前服务器时间验证失败
        if (isset($payload['iat']) && $payload['iat'] > time()) {
            return false;
        }

        // 生效时间大于当前服务器时间验证失败
        if (isset($payload['nbf']) && $payload['nbf'] > time()) {
            return false;
        }

        // 当前服务器时间大于过期时间验证失败
        if (isset($payload['ref_exp']) && time() > $payload['ref_exp']) {
            return false;
        }

        return true;
    }

    /**
     * 验证签名
     * @param string $token
     * @return bool
     */
    private static function verifySign($token)
    {
        $signer = new Sha256();
        $parse  = new Parser();
        $parse  = $parse->parse($token);
        $result = $parse->verify($signer, self::$key);// 验证成功返回true 失败false

        return $result;
    }

    /**
     * 使Token失效
     * @param string $token
     * @return bool
     */
    public static function invalidate($token)
    {
        return  '';
    }

    /**
     * 将token加入黑名单
     * @param string $token
     * @return bool
     */
    public static function addBlacklist($token)
    {
        $token_redis_ex=(self::getPayload($token))['exp'] - time();
        $result = Redis::setex($token,$token_redis_ex,$token);// 设置成功返回true 失败false

        return $result;
    }
}