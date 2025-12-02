<?php

namespace App\Constants;

class ValidationConstants
{
    public const ERROR = 'Validation errors.';
    public const REQUIRED = 'This field is required.';
    public const STRING = 'This field must be a string.';
    public const MAX = 'The maximum length has been exceeded.';
    public const IMAGE = 'This field must be an image.';
    public const MIMES = 'Invalid file type. Allowed types: jpeg, png, jpg, gif.';
    public const BOOLEAN = 'This field must be a boolean value.';
    public const UNIQUE = 'This value already exists.';
}
