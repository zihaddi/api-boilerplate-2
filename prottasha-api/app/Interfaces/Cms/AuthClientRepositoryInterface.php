<?php

namespace App\Interfaces\Cms;

interface AuthClientRepositoryInterface
{
    public function login($obj, $data);
    public function refreshToken($request);
    public function getUser($request);
}
