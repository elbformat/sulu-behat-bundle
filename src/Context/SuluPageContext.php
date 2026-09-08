<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentCollection;
use Sulu\Messenger\Infrastructure\Symfony\Messenger\FlushMiddleware\EnableFlushStamp;
use Sulu\Page\Application\Message\ApplyWorkflowTransitionPageMessage;
use Sulu\Page\Application\Message\CreatePageMessage;
use Sulu\Page\Application\Message\ModifyPageMessage;
use Sulu\Page\Domain\Model\PageDimensionContent;
use Sulu\Page\Domain\Model\PageInterface;
use Sulu\Page\Domain\Repository\PageRepositoryInterface;
use Sulu\Route\Domain\Repository\RouteRepositoryInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

/**
 * Create pages and add modules.
 *
 * @phpstan-import-type InputData from AbstractSuluContext
 */
class SuluPageContext extends AbstractSuluContext
{
    use HandleTrait;

    protected ?PageInterface $lastPage = null;

    public function __construct(
        EntityManagerInterface $entityManager,
        WebspaceManagerInterface $webspaceManager,
        MessageBusInterface $messageBus,
        protected PageRepositoryInterface $pageRepository,
        protected RouteRepositoryInterface $routeRepository,
    ) {
        parent::__construct($entityManager, $webspaceManager);
        $this->messageBus = $messageBus;
    }

    #[BeforeScenario]
    public function reset(): void
    {
        $this->exec('DELETE FROM ro_routes WHERE slug != "/"');
        $this->exec('DELETE FROM pa_page_dimension_contents WHERE pageUuid NOT IN (SELECT resource_id FROM ro_routes)');
        $this->exec('DELETE FROM pa_pages WHERE parent_id IS NOT NULL');
    }

    #[Given('there is a(n) :template page')]
    #[Given('there is a(n) page')]
    public function thereIsAPage(?TableNode $tableNode = null, ?PyStringNode $pyStringNode = null, ?string $template = null): void
    {
        $data = $this->getData($tableNode, $pyStringNode);
        $data['template'] = $template ?? null;
        $data = $this->applyDefaults($data);
        $this->lastPage = $this->createPage($data);

        if ('publish' === $data['_action']) {
            $this->publishPage($this->lastPage);
        }
    }

    #[Given('the page contains a(n) :moduleName module in :blockName')]
    public function thePageContainsAModuleIn(string $moduleName, string $blockName, ?TableNode $table = null, ?PyStringNode $pyStringNode = null): void
    {
        $data = $this->getData($table, $pyStringNode);
        $data['type'] = $moduleName;
        $this->addModule($data, $blockName);
    }

    /**
     * @param array<string|int,mixed> $moduleData
     */
    protected function addModule(array $moduleData, ?string $blockName = null, ?PageInterface $page = null): void
    {
        $page ??= $this->lastPage;
        Assert::isInstanceOf($page, PageInterface::class);
        $locale = $this->getDefaultLocale($page->getWebspaceKey());
        $blockName ??= $this->getDefaultBlock();
        $dimensionContentCollection = new DimensionContentCollection($page->getDimensionContents(), [], PageDimensionContent::class);
        $template = $dimensionContentCollection->getDimensionContent(['locale' => $locale])?->getTemplateKey() ?? 'default';

        $data = [
            'locale' => $locale,
            'template' => $template,
            $blockName => [
                $moduleData,
            ],
        ];

        $this->updatePage($data);
        $this->publishPage($page, $locale);
    }

    /** @param InputData $data */
    protected function createPage(array $data): PageInterface
    {
        Assert::keyExists($data, '_webspaceKey');
        Assert::stringNotEmpty($data['_webspaceKey']);
        $webspaceKey = $data['_webspaceKey'];
        unset($data['_webspaceKey']);

        Assert::keyExists($data, '_parentId');
        Assert::stringNotEmpty($data['_parentId']);
        $parentId = $data['_parentId'];
        unset($data['_parentId']);

        $message = new CreatePageMessage($webspaceKey, $parentId, $data);
        $page = $this->handle(new Envelope($message, [new EnableFlushStamp()]));
        Assert::isInstanceOf($page, PageInterface::class);

        return $page;
    }

    /** @param array<string, mixed> $data */
    protected function updatePage(array $data, ?PageInterface $page = null): void
    {
        $page ??= $this->lastPage;
        Assert::isInstanceOf($page, PageInterface::class);
        $data['locale'] ??= $this->getDefaultLocale($page->getWebspaceKey());
        Assert::string($data['locale']);
        $data['template'] ??= $this->getTemplate($page, $data['locale']);

        // @see Sulu\Page\UserInterface\Controller\Admin\PageController::putAction()
        $message = new ModifyPageMessage(['uuid' => $page->getUuid()], $data);
        $this->handle(new Envelope($message, [new EnableFlushStamp()]));
    }

    protected function publishPage(PageInterface $page, ?string $locale = null): void
    {
        $locale ??= $this->getDefaultLocale($page->getWebspaceKey());
        $message = new ApplyWorkflowTransitionPageMessage(['uuid' => $page->getUuid()], $locale, 'publish');
        /* @see \Sulu\Page\Application\MessageHandler\ApplyWorkflowTransitionPageMessageHandler */
        $this->handle(new Envelope($message, [new EnableFlushStamp()]));
    }

    protected function buildUrl(string $parentId, string $title): string
    {
        return $this->getSlugById($parentId).'/'.urlencode($title);
    }

    protected function getRootId(string $webspaceKey): string
    {
        return $this->pageRepository->getOneBy([
            'webspaceKey' => $webspaceKey,
            'parentId' => null,
        ], [
            'uuid' => 'uuid',
        ])->getUuid();
    }

    protected function getIdBySlug(string $slug, string $webspaceKey): string
    {
        $route = $this->routeRepository->getOneBy([
            'webspaceKey' => $webspaceKey,
            'slug' => $slug,
        ]);
        if ($route->hasTemporaryId()) {
            return $route->generateRealResourceId();
        }

        return $route->getResourceId();
    }

    protected function getSlugById(string $uuid): string
    {
        return $this->routeRepository->getOneBy([
            'resourceId' => $uuid,
        ])->getSlug();
    }

    protected function getDefaultBlock(): string
    {
        // @todo find in template config
        return 'block';
    }

    protected function getTemplate(PageInterface $page, string $locale): string
    {
        $dimensionContentCollection = new DimensionContentCollection($page->getDimensionContents(), [], PageDimensionContent::class);

        return $dimensionContentCollection->getDimensionContent(['locale' => $locale])?->getTemplateKey() ?? 'default';
    }

    /**
     * Defaults for creating a page.
     *
     * @param InputData $data
     *
     * @return InputData
     */
    protected function applyDefaults(array $data): array
    {
        // Metadata
        $data['_webspaceKey'] ??= $this->getWebspaceKey();
        Assert::string($data['_webspaceKey']);
        if (isset($data['_parentSlug']) && \is_string($data['_parentSlug'])) {
            $data['_parentId'] = $this->getIdBySlug($data['_parentSlug'], $data['_webspaceKey']);
            unset($data['_parentSlug']);
        } else {
            $data['_parentId'] ??= $this->getRootId($data['_webspaceKey']);
        }
        Assert::string($data['_parentId']);
        $data['_action'] ??= 'publish';
        $data['locale'] ??= $this->getDefaultLocale($data['_webspaceKey']);

        // Content data
        $data['template'] ??= $this->getDefaultTemplate();
        $data['title'] ??= 'new-page';
        Assert::string($data['title']);
        $data['url'] ??= $this->buildUrl($data['_parentId'], $data['title']);

        return $data;
    }
}
