<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Elbformat\SymfonyBehatBundle\Context\BrowserContext as SymfonyBrowserContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
class BrowserContext extends SymfonyBrowserContext
{
    /**
     * @Given I am logged in as admin
     */
    public function iAmLoggedInAsAdmin(): void
    {
        $jsonData = json_encode(['username' => 'admin', 'password' => 'admin'], JSON_THROW_ON_ERROR);
        $server = ['CONTENT_TYPE' => 'application/json'];
        $this->doRequest(Request::create('/admin/login', 'POST', [], [], [], $server, $jsonData));
    }
}
