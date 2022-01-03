<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle;

use Behat\Gherkin\Node\TableNode;
use Sulu\Bundle\PageBundle\Document\PageDocument;
use Sulu\Bundle\PageBundle\Form\Type\PageDocumentType;

/**
 * Simulates the admin part of sulu.
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
final class SuluContext extends PhpCrContext
{
    /** @var PageDocument|null */
    protected $lastDocument;

    /**
     * Clear all phpcr contents before each scenario
     *
     * @BeforeScenario @sulu
     */
    public function resetDatabase(): void
    {
        $this->exec('DELETE FROM phpcr_nodes WHERE id >= 1000');
        $this->exec('ALTER TABLE phpcr_nodes AUTO_INCREMENT=1000');
    }

    /**
     * @Given there is a(n) :alias page
     */
    public function thereIsAPage(string $alias, TableNode $tableNode): void
    {
        /** @var PageDocument $document */
        $document = $this->docManager->create('page');

        $data = $tableNode->getRowsHash();
        $data['template'] = $alias;

        $this->saveDocument($document, $this->expandData($data), PageDocumentType::class, self::WEBSPACE);

        $this->lastDocument = $document;
    }

    /**
     * @Given the page contains a(n) :moduleName module in :blockName
     */
    public function thePageContainsAModuleIn(string $moduleName, string $blockName, TableNode $table = null)
    {
        if (null === $this->lastDocument) {
            throw new \DomainException('You need to create a document first');
        }

        $data = [
            $blockName => [
                [
                    'type' => $moduleName,
                ],
            ],
        ];
        $blockData = null !== $table ? $table->getRowsHash() : [];
        $blockData = $this->expandData($blockData);
        foreach ($blockData as $key => $val) {
            $data[$blockName][0][$key] = $this->isJson($moduleName, $key) ? \json_decode($val, true, 512, JSON_THROW_ON_ERROR) : $val;
        }

        $this->saveDocument($this->lastDocument, $data, PageDocumentType::class, self::WEBSPACE);
    }

    public function getLastDocument(): ?PageDocument
    {
        return $this->lastDocument;
    }
}
