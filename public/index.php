<?php

declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

$careCloudClient = new \App\Model\CareCloud\CareCloudClient(
    new \App\Config\CareCloudAppId(),
    new \App\Config\CareCloudPassword(),
    new \App\Config\CareCloudUser()
);
$application = new \App\Application(
    new \App\Model\BigCommerce\UserCreate(
        $careCloudClient,
        new \App\Model\BigCommerce\BigCommerceUserFactory()
    ),
    new \App\Model\BigCommerce\UserUpdate(
        $careCloudClient
    ),
    new \App\Model\BigCommerce\OrderCreate(
        $careCloudClient
    ),
);

$request = new \GuzzleHttp\Psr7\Request(
    'POST',
    $_SERVER['REQUEST_URI'],
    body: $_POST,
);

$application->run($request);
