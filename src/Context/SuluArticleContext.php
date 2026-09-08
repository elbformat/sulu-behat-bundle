<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Doctrine\ORM\EntityManagerInterface;
use ONGR\ElasticsearchBundle\Service\Manager;
use Sulu\Article\Domain\Model\ArticleInterface;
use Sulu\Article\Domain\Repository\ArticleRepositoryInterface;
use Sulu\Bundle\ArticleBundle\Document\ArticleDocument;
use Sulu\Bundle\ArticleBundle\Document\Form\ArticleDocumentType;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Page\Domain\Model\PageInterface;
use Sulu\Page\Domain\Repository\PageRepositoryInterface;
use Sulu\Route\Domain\Repository\RouteRepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Create articles.
 */
class SuluArticleContext extends AbstractContentRichContext
{
    use HandleTrait;

    protected ?ArticleInterface $lastArticle = null;

    public function __construct(
        EntityManagerInterface $entityManager,
        WebspaceManagerInterface $webspaceManager,
        MessageBusInterface $messageBus,
        protected ArticleRepositoryInterface $articleRepository,
    ) {
        parent::__construct($entityManager, $webspaceManager);
        $this->messageBus = $messageBus;
    }

    #[BeforeScenario]
    public function reset(): void
    {
        $this->exec('DELETE FROM ar_article_dimension_contents');
        $this->exec('DELETE FROM ar_articles');
    }

    #[Given('there is a(n) :type article :alias')]
    public function thereIsAnArticle(string $type, string $alias, ?TableNode $tableNode = null): void
    {
        /** @var ArticleDocument $document */
        $document = $this->docManager->create('article');
        $document->setStructureType($type);

        /** @var array<string,string> $data */
        $data = null !== $tableNode ? $tableNode->getRowsHash() : [];

        $this->saveDocument($document, $this->expandData($data), ArticleDocumentType::class);

        $this->lastDocument = $document;
    }

    #[Given('the article contains a(n) :moduleName module in :blockName')]
    public function theArticleContainsAModuleIn(string $moduleName, string $blockName, ?TableNode $table = null): void
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
