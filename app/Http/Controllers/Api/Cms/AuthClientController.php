<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\Auth\AuthClientRequest;
use App\Interfaces\Cms\AuthClientRepositoryInterface;
use App\Models\AuthClient;
use Illuminate\Http\Request;

class AuthClientController extends Controller
{
    protected $client;

    public function __construct(AuthClientRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function login(AuthClient $obj, AuthClientRequest $request)
    {
        return $this->client->login($obj, $request);
    }

    public function refreshToken(Request $request)
    {
        return $this->client->refreshToken($request);
    }

    public function getUser(Request $request)
    {
        return $this->client->getUser($request);
    }
}
