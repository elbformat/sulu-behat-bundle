<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use ONGR\ElasticsearchBundle\Service\Manager;
use Sulu\Bundle\ArticleBundle\Document\ArticleDocument;
use Sulu\Bundle\ArticleBundle\Document\Form\ArticleDocumentType;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Simulates the admin part of sulu for articles.
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
final class SuluArticleContext extends AbstractPhpCrContext
{
    protected ?ArticleDocument $lastDocument = null;

    protected Manager $esManager;

    public function __construct(EntityManagerInterface $em, WebspaceManagerInterface $webspaceManager, DocumentManagerInterface $docManager, FormFactoryInterface $formFactory, Manager $esManager)
    {
        parent::__construct($em, $webspaceManager, $docManager, $formFactory);
        $this->esManager = $esManager;
    }

    /**
     * Clear all ES contents before each scenario
     *
     * @BeforeScenario
     */
    public function resetElasticSearch(): void
    {
        $this->esManager->dropAndCreateIndex();
    }

    /**
     * @Given there is a(n) :type article :alias
     */
    public function thereIsAnArticle(string $type, string $alias, TableNode $tableNode = null): void
    {
        /** @var ArticleDocument $document */
        $document = $this->docManager->create('article');
        $document->setStructureType($type);

        /** @var array<string,string> $data */
        $data = null !== $tableNode ? $tableNode->getRowsHash() : [];

        $this->saveDocument($document, $this->expandData($data), ArticleDocumentType::class);

        $this->lastDocument = $document;
    }

    /**
     * @Given the article contains a(n) :moduleName module in :blockName
     */
    public function theArticleContainsAModuleIn(string $moduleName, string $blockName, TableNode $table = null): void
    {
        if (null !== $table) {
            /** @var array<string, string> $tableData */
            $tableData = $table->getRowsHash();
            $data = $this->expandData($tableData);
        } else {
            $data = [];
        }
        $this->addModule($moduleName, $blockName, $data, ArticleDocumentType::class);
    }

    protected function getLastDocument(): ArticleDocument
    {
        if (null === $this->lastDocument) {
            throw new \DomainException('No document queried.');
        }
        return $this->lastDocument;
    }
}
