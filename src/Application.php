<?php

declare(strict_types = 1);

namespace App;

use App\Model\OrderCreateInterface;
use App\Model\UserCreateInterface;
use App\Model\UserUpdateInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Application
{

    public function __construct(
        private readonly UserCreateInterface $userCreate,
        private readonly UserUpdateInterface $userUpdate,
        private readonly OrderCreateInterface $orderCreate,
    ) {}

    public function run(RequestInterface $request): ResponseInterface
    {
        return match ($request->getRequestTarget())
        {
            AvailableHook::USER_CREATE_URL => $this->userCreate->process($request),
            AvailableHook::USER_UPDATE_URL => $this->userUpdate->process($request),
            AvailableHook::ORDER_CREATE_URL => $this->orderCreate->process($request),
            default => new Response(body: 'Not valid hook.'),
        };
    }

}
