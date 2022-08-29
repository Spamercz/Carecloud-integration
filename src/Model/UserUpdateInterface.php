<?php

declare(strict_types = 1);

namespace App\Model;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface UserUpdateInterface
{

    public function process(RequestInterface $request): ResponseInterface;

}
