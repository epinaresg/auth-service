<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class UnexpectedException extends ApiException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, 'Unexpected error', $previous);
    }
}
