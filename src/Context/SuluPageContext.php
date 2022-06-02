<?php declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Gherkin\Node\TableNode;
use Sulu\Bundle\PageBundle\Document\PageDocument;
use Sulu\Bundle\PageBundle\Form\Type\PageDocumentType;

/**
 * Simulates the admin part of sulu.
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
final class SuluPageContext extends PhpCrContext
{
    protected ?PageDocument $lastDocument = null;

    /**
     * Clear all phpcr contents before each scenario
     *
     * @BeforeScenario @sulu
     */
    public function resetDatabase(): void
    {
        $this->exec('DELETE FROM phpcr_nodes WHERE id >= 1000');
        $this->exec('ALTER TABLE phpcr_nodes AUTO_INCREMENT=1000');
        $this->exec('DELETE FROM phpcr_nodes_references WHERE source_id >= 1000 OR target_id >= 1000');
        $this->exec("UPDATE phpcr_nodes set props = REGEXP_REPLACE(props, '<sv.+settings:snippets-.+<\/sv\:property>', '')");
    }

    /**
     * @Given there is a(n) :alias page
     * @Given there is a(n) page
     * @Given there is a(n) :alias page as child of :parent
     * @Given there is a(n) page as child of :parent
     */
    public function thereIsAPage(TableNode $tableNode, string $alias = 'default', string $parent = null): void
    {
        /** @var PageDocument $document */
        $document = $this->docManager->create('page');

        $data = $tableNode->getRowsHash();
        $data['template'] = $alias;

        $this->saveDocument($document, $this->expandData($data), PageDocumentType::class, null, $parent);

        $this->addToRouteStack($document->getResourceSegment(), $document->getUuid());
        $this->lastDocument = $document;
    }

    /**
     * @Given the page contains a(n) :moduleName module in :blockName
     */
    public function thePageContainsAModuleIn(string $moduleName, string $blockName, TableNode $table = null): void
    {
        $this->addModule($moduleName, $blockName, $table ? $table->getRowsHash() : []);
    }

    protected function getLastDocument(): PageDocument
    {
        if (null === $this->lastDocument) {
            throw new \DomainException('No document queried.');
        }

        return $this->lastDocument;
    }
}
