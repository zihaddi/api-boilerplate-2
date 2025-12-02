<?php

namespace App\Repositories\Cms;

use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Enums\TokenAbility;
use App\Http\Traits\Access;
use App\Http\Traits\HttpResponses;
use App\Interfaces\Cms\AuthClientRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Hash;
use PHPUnit\TextUI\Configuration\Constant;

class AuthClientRepository implements AuthClientRepositoryInterface
{
    use Access;
    use HttpResponses;

    protected $auth_guard_name = '';

    /**
     * __construct
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->auth_guard_name = config('services.cms_auth_guard.name');
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function login($obj, $request)
    {
        try {
            $loginData = [];
            $loginData = ['email' => $request['email'], 'password' => $request['password']];
            if (Auth::guard($this->auth_guard_name)->attempt($loginData)) {

                $user = $obj::where('id', Auth::guard($this->auth_guard_name)->id())->first();

                $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
                $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

                $success['token'] = $accessToken->plainTextToken;
                //  $success['expires_at'] = $accessToken->expires_at;
                $success['expire_time'] = config('sanctum.ac_expiration');
                $success['refresh_token'] = $refreshToken->plainTextToken;
                return $this->success($success, AuthConstants::LOGIN, Response::HTTP_OK, true);
            } else {
                return $this->error(null, AuthConstants::VALIDATION, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function refreshToken($request)
    {
        try {
            $accessToken = $request->user()->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            $success['expire_time'] = config('sanctum.ac_expiration');
            $success['token'] = $accessToken->plainTextToken;
            return $this->success($success, AuthConstants::TOCKENREGENERATE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }


    public function getUser($request)
    {
        try {
            $user = $request->user();
            return $this->success($user, Constants::GETALL, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
