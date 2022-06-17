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

    /**
     * @BeforeScenario
     */
    public function resetRoutes(): void
    {
        // @rfe run only once per scenarion (and not per inheriting context)
        $this->exec('DELETE FROM ro_routes');
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

    /**
     * Convert data with dot notation into nested array structure
     *
     * @param array<string,string> $data
     * @return array<string,mixed>
     */
    protected function expandData(array $data): array
    {
        $newData = [];
        foreach ($data as $k => $v) {
            // Plain key
            if (false === strpos($k, '.')) {
                $newData[$k] = $this->replacePlaceholders($v);
                continue;
            }
            $parts = explode('.', $k, 2);
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
