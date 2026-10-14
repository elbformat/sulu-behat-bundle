<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Elbformat\SymfonyBehatBundle\Context\TableOrStringTrait;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

/**
 * Commons for database reset and argument handling.
 */
abstract class AbstractSuluContext implements Context
{
    use TableOrStringTrait;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected WebspaceManagerInterface $webspaceManager,
    ) {
    }

    protected function exec(string $query): void
    {
        $this->entityManager->getConnection()->executeQuery($query);
    }

    protected function getDefaultWebspaceKey(): string
    {
        $webspaces = $this->webspaceManager->getWebspaceCollection()->getWebspaces();
        if (!$webspaces) {
            throw new \DomainException('No webspaces found!');
        }

        return (string) array_keys($webspaces)[0];
    }

    protected function getDefaultLocale(?string $webspaceKey = null): string
    {
        $webspaceKey ??= $this->getDefaultWebspaceKey();
        $webspace = $this->webspaceManager->findWebspaceByKey($webspaceKey);
        if (null === $webspace) {
            throw new \DomainException(\sprintf('Webspace %s not found!', $webspaceKey));
        }

        return $webspace->getDefaultLocalization()->getLanguage();
    }

    protected function getDefaultTemplate(string $webspaceKey, string $type): string
    {
        $webspace = $this->webspaceManager->findWebspaceByKey($webspaceKey);

        return $webspace?->getDefaultTemplate($type) ?? 'default';
    }
}
