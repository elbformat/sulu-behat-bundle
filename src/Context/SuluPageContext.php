<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Gherkin\Node\TableNode;
use Sulu\Bundle\PageBundle\Document\PageDocument;
use Sulu\Bundle\PageBundle\Form\Type\PageDocumentType;

/**
 * Simulates the admin part of sulu.
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
final class SuluPageContext extends AbstractPhpCrContext
{
    protected ?PageDocument $lastDocument = null;

    /**
     * @Given there is a(n) :template page
     * @Given there is a(n) page
     * @Given there is a(n) :template page as child of :parentId
     * @Given there is a(n) page as child of :parentId
     */
    public function thereIsAPage(TableNode $tableNode, string $template = 'default', int $parentId = null): void
    {
        /** @var PageDocument $document */
        $document = $this->docManager->create('page');

        $data = $tableNode->getRowsHash();
        $data['template'] = $template;

        $this->saveDocument($document, $this->expandData($data), PageDocumentType::class, true, $parentId);

        $this->lastDocument = $document;
    }

    /**
     * @Given the page contains a(n) :moduleName module in :blockName
     */
    public function thePageContainsAModuleIn(string $moduleName, string $blockName, TableNode $table = null): void
    {
        if (null !== $table) {
            $data = $this->expandData($table->getRowsHash());
        }
        $this->addModule($moduleName, $blockName, $data ?? []);
    }

    protected function getLastDocument(): PageDocument
    {
        if (null === $this->lastDocument) {
            throw new \DomainException('No document queried.');
        }

        return $this->lastDocument;
    }
}
