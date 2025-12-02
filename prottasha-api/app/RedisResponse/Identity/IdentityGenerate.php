<?php

namespace App\RedisResponse\Identity;

class IdentityGenerate
{
    public $fullUrl;
    public $apiRoute;
    public $redisKey;

    public function __construct()
    {
        $appUrl = config('app.url').'/';
        $currentFullRoute = URL()->current();
        $currentRoute = str_replace($appUrl, '', $currentFullRoute);

        $uri = explode('/',$currentRoute);

        if(isset($uri[0]))
        {
            $baseUrl = $appUrl.$uri[0];

            $this->fullUrl = $baseUrl;
            $this->apiRoute = $uri[0];

            $this->redisKey = 'API-'.$uri[0].'-'.strtoupper(md5(json_encode(request()->all(), JSON_UNESCAPED_UNICODE)));
        }
    }
}
