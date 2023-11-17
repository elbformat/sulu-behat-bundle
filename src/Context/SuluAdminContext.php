<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Behat\Context\Context;
use Elbformat\SymfonyBehatBundle\Browser\State;
use Elbformat\SymfonyBehatBundle\Context\RequestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
class SuluAdminContext implements Context
{
    use RequestTrait;

    public function __construct(
        protected State $state,
        protected KernelInterface $kernel,
    ) {

    }

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
