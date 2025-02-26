<?php

namespace abenevaut\Linkedin\Client;

use abenevaut\Infrastructure\Client\AuthenticatedClientAbstract;

final class LinkedinClient extends AuthenticatedClientAbstract
{
    public function __construct(
        string $baseUrl,
        AccessToken $accessToken,
        bool $debug = false
    ) {
        parent::__construct($baseUrl, $accessToken, $debug);
    }

    public function getGroup(string $groupId, array $scopes = ['id']): array
    {
        $scopesInLine = implode(',', $scopes);

        return $this
            ->request()
            ->get("groups/{$groupId}?projection=({$scopesInLine})")
            ->throw()
            ->json();
    }

    public function getCompanyFollowerStatistics(string $companyId): array
    {
        return $this
            ->request()
            ->get("/organizationalEntityFollowerStatistics?q=organizationalEntity&organizationalEntity=urn:li:organization:{$companyId}")
            ->throw()
            ->json();
    }
}
