<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class UnauthorizedException extends ApiException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(Response::HTTP_UNAUTHORIZED, 'Unauthorized', $previous);
    }
}
