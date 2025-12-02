<?php 

namespace App\RedisResponse;

use App\RedisResponse\Output\Output;
use App\RedisResponse\Identity\IdentityGenerate;

class BaseClassForResponse {
    public static function output()
    {
        return new Output();
    }

    public static function route()
    {
        return new IdentityGenerate();
    }
}