<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

/**
 * Commons for database reset and argument handling.
 * This type allows a lot, but no objects                         v-- Theoretically we need InputData here.
 *
 * @phpstan-type InputData array<string,null|scalar|array<string,mixed>>
 */
abstract class AbstractSuluContext implements Context
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected WebspaceManagerInterface $webspaceManager,
    ) {
    }

    protected function exec(string $query): void
    {
        $this->entityManager->getConnection()->executeQuery($query);
    }

    protected function getWebspaceKey(): string
    {
        $webspaces = $this->webspaceManager->getWebspaceCollection()->getWebspaces();
        if (!$webspaces) {
            throw new \DomainException('No webspaces found!');
        }
        // @todo match by hostname

        return (string) array_keys($webspaces)[0];
    }

    protected function getDefaultLocale(?string $webspaceKey = null): string
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
     * Convert tables/json/yaml to a (nested) array structure with scalars (no objects).
     *
     * @return InputData
     */
    protected function getData(?TableNode $tableNode = null, ?PyStringNode $yaml = null): array
    {
        if (null !== $tableNode) {
            // @todo make structure from flat data with property access
            /** @var array<string, string> $data */
            $data = $tableNode->getRowsHash();
        // @todo replace NULL values
        } elseif (null !== $yaml) {
            // @todo check if it's yaml or json
            $cleanYaml = $this->removeCommonIndentation($yaml->getRaw());

            $data = Yaml::parse($cleanYaml);
            Assert::isMap($data);
            foreach ($data as $entry) {
                if (\is_scalar($entry)) {
                    continue;
                }
                if (!\is_array($entry)) {
                    throw new \InvalidArgumentException('yaml contains other than scalar values');
                }
                Assert::isMap($entry);
            }
        } else {
            $data = [];
        }

        return $data;
    }

    protected function removeCommonIndentation(string $input): string
    {
        $lines = explode("\n", $input);

        // Determine the number of leading spaces in the first line
        $firstLine = $lines[0];
        $leadingSpaces = \strlen($firstLine) - \strlen(ltrim($firstLine));

        // Remove the same number of leading spaces from each line
        $outputLines = array_map(static fn ($line) => substr($line, $leadingSpaces), $lines);

        // Join the modified lines back into a single string
        return implode("\n", $outputLines);
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
            if (!str_contains((string) $k, '.')) {
                $newData[$k] = $this->replacePlaceholders($v);
                continue;
            }
            $parts = explode('.', (string) $k, 2);
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
