<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

/**
 * Webspace aware database context.
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
abstract class AbstractSuluContext extends AbstractDatabaseContext
{
    protected WebspaceManagerInterface $webspaceManager;

    public function __construct(EntityManagerInterface $em, WebspaceManagerInterface $webspaceManager)
    {
        parent::__construct($em);
        $this->webspaceManager = $webspaceManager;
    }

    protected function getWebspaceKey(): string
    {
        $webspaces = $this->webspaceManager->getWebspaceCollection()
            ->getWebspaces();
        if (!$webspaces) {
            throw new \DomainException('No webspaces found!');
        }

        return (string) array_keys($webspaces)[0];
    }

    protected function getLocale(): string
    {
        $webspaceKey = $this->getWebspaceKey();
        $webspace = $this->webspaceManager->findWebspaceByKey($webspaceKey);
        if (null === $webspace) {
            throw new \DomainException(sprintf('Webspace %s not found!', $webspaceKey));
        }
        return $webspace->getDefaultLocalization()->getLanguage();
    }
}
