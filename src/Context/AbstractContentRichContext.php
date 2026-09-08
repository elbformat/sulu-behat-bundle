<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Domain\Model\ContentRichEntityInterface;

/**
 * Commons for ContentRichEntityInterface
 */
abstract class AbstractContentRichContext implements Context
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected WebspaceManagerInterface $webspaceManager,
    ) {}

    protected function exec(string $query): void
    {
        $this->em->getConnection()->executeQuery($query);
    }

    protected function getWebspaceKey(): string
    {
        $webspaces = $this->webspaceManager->getWebspaceCollection()->getWebspaces();
        if (!$webspaces) {
            throw new \DomainException('No webspaces found!');
        }
        // @todo match by hostname

        return (string)array_keys($webspaces)[0];
    }

    protected function getDefaultLocale(?string $webspaceKey=null): string
    {
        $webspaceKey ??= $this->getWebspaceKey();
        $webspace = $this->webspaceManager->findWebspaceByKey($webspaceKey);
        if (null === $webspace) {
            throw new \DomainException(\sprintf('Webspace %s not found!', $webspaceKey));
        }

        return $webspace->getDefaultLocalization()->getLanguage();
    }

    protected function getDefaultTemplate(): string
    {
        // @todo look into the config
        return 'default';
    }

    /**
     * Convert data with dot notation into nested array structure.
     *
     * @param array<string|int,string> $data
     *
     * @return array<string|int,mixed>
     */
    protected function expandData(array $data): array
    {
        // @todo use symfony propertyaccess syntax instead (see https://symfony.com/doc/7.4/components/property_access.html#writing-to-arrays)
        $newData = [];
        foreach ($data as $k => $v) {
            // Plain key
            if (!str_contains((string)$k, '.')) {
                $newData[$k] = $this->replacePlaceholders($v);
                continue;
            }
            $parts = explode('.', (string)$k, 2);
            $deepStructure = $this->expandData([$parts[1] => $v]);
            /** @var array<string,mixed> $existing */
            $existing = $newData[$parts[0]] ?? [];
            $newData[$parts[0]] = array_merge_recursive($existing, $deepStructure);
        }

        return $newData;
    }

    /** to be overridden by extending contexts */
    protected function replacePlaceholders(string $value): string
    {
        // Replace line breaks
        $value = str_replace('\n', "\n", $value);

        return $value;
    }
}
