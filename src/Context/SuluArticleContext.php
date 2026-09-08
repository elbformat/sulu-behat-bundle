<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Doctrine\ORM\EntityManagerInterface;
use Elbformat\SymfonyBehatBundle\Context\NestedMapTrait;
use Sulu\Article\Application\Message\CreateArticleMessage;
use Sulu\Article\Domain\Model\ArticleDimensionContent;
use Sulu\Article\Domain\Model\ArticleInterface;
use Sulu\Article\Domain\Repository\ArticleRepositoryInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentCollection;
use Sulu\Messenger\Infrastructure\Symfony\Messenger\FlushMiddleware\EnableFlushStamp;
use Sulu\Page\Application\Message\ApplyWorkflowTransitionPageMessage;
use Sulu\Page\Application\Message\ModifyPageMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

/**
 * Create articles.
 *
 * @phpstan-import-type NestedMap from NestedMapTrait
 */
class SuluArticleContext extends AbstractSuluContext
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

    #[Given('there is a(n) :template article :alias')]
    public function thereIsAnArticle(string $template, string $alias, ?TableNode $tableNode = null): void
    {
        $data['template'] = $template;

        $data = $this->applyDefaults($this->getDataFromTableOrString($tableNode));
        $this->lastArticle = $this->createArticle($data);

        if ('publish' === $data['_action']) {
            $this->publishArticle($this->lastArticle);
        }
    }

    #[Given('the article contains a(n) :moduleName module in :blockName')]
    public function theArticleContainsAModuleIn(string $moduleName, string $blockName, ?TableNode $table = null): void
    {
        $data = $this->getDataFromTableOrString($table);
        $data['type'] = $moduleName;
        $this->addModule($data, $blockName);
    }

    /**
     * @param NestedMap $moduleData
     */
    protected function addModule(array $moduleData, ?string $blockName = null, ?ArticleInterface $article = null): void
    {
        $article ??= $this->lastArticle;
        Assert::string($article);
        $locale = $this->getDefaultLocale();
        $blockName ??= $this->getDefaultBlock();
        Assert::string($blockName);

        $data = [
            'locale' => $locale,
            $blockName => [
                $moduleData,
            ],
        ];

        $this->updateArticle($data);
        $this->publishArticle($article, $locale);
    }

    /** @param NestedMap $data */
    protected function createArticle(array $data): ArticleInterface
    {
        $message = new CreateArticleMessage($data);
        $article = $this->handle(new Envelope($message, [new EnableFlushStamp()]));
        Assert::isInstanceOf($article, ArticleInterface::class);

        return $article;
    }

    /** @param NestedMap $data */
    protected function updateArticle(array $data, ?ArticleInterface $article = null): void
    {
        $article ??= $this->lastArticle;
        Assert::isInstanceOf($article, ArticleInterface::class);
        $data['locale'] ??= $this->getDefaultLocale();
        Assert::string($data['locale']);
        $data['template'] ??= $this->getTemplate($article, $data['locale']);
        Assert::string($data['template']);

        // @see Sulu\Page\UserInterface\Controller\Admin\PageController::putAction()
        $message = new ModifyPageMessage(['uuid' => $article->getUuid()], $data);
        $this->handle(new Envelope($message, [new EnableFlushStamp()]));
    }

    protected function publishArticle(ArticleInterface $article, ?string $locale = null): void
    {
        $locale ??= $this->getDefaultLocale();
        $message = new ApplyWorkflowTransitionPageMessage(['uuid' => $article->getUuid()], $locale, 'publish');
        /* @see \Sulu\Article\Application\MessageHandler\ApplyWorkflowTransitionArticleMessageHandler */
        $this->handle(new Envelope($message, [new EnableFlushStamp()]));
    }

    protected function getTemplate(ArticleInterface $article, string $locale): string
    {
        $dimensionContentCollection = new DimensionContentCollection($article->getDimensionContents(), [], ArticleDimensionContent::class);

        return $dimensionContentCollection->getDimensionContent(['locale' => $locale])?->getTemplateKey() ?? 'default';
    }

    protected function getDefaultBlock(): string
    {
        // Improvement: find out via config
        return 'block';
    }

    /**
     * Defaults for creating an article.
     *
     * @param NestedMap $data
     *
     * @return NestedMap
     */
    protected function applyDefaults(array $data): array
    {
        // Metadata
        $data['_webspaceKey'] ??= $this->getDefaultWebspaceKey();
        Assert::string($data['_webspaceKey']);
        $data['_action'] ??= 'publish';
        $data['locale'] ??= $this->getDefaultLocale($data['_webspaceKey']);

        // Content data
        $data['template'] ??= $this->getDefaultTemplate($data['_webspaceKey'], ArticleInterface::TEMPLATE_TYPE);
        $data['title'] ??= 'new-article';

        return $data;
    }
}
