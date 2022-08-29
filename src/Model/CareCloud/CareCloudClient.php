<?php

declare(strict_types = 1);

namespace App\Model\CareCloud;

use App\Config\CareCloudAppId;
use App\Config\CareCloudPassword;
use App\Config\CareCloudUser;
use CrmCareCloud\Webservice\RestApi\Client\SDK\CareCloud;
use CrmCareCloud\Webservice\RestApi\Client\SDK\Config;
use CrmCareCloud\Webservice\RestApi\Client\SDK\Data\AuthTypes;

final class CareCloudClient
{

    public function __construct(
        private readonly CareCloudAppId $careCloudAppId,
        private readonly CareCloudPassword $careCloudPassword,
        private readonly CareCloudUser $careCloudUser
    ) {}

    public function provide(): CareCloud
    {
        $config = new Config(
            \App\Model\CareCloud\Config::PROJECT_URI,
            $this->careCloudUser->provide(),
            $this->careCloudPassword->provide(),
            $this->careCloudAppId->provide(),
            AuthTypes::BEARER_AUTH,
        );

        return new CareCloud($config);
    }

}
