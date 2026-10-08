<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Messenger\Infrastructure\Symfony\Messenger\FlushMiddleware\EnableFlushStamp;
use Sulu\Snippet\Application\Message\ApplyWorkflowTransitionSnippetMessage;
use Sulu\Snippet\Application\Message\CreateSnippetMessage;
use Sulu\Snippet\Application\Message\ModifySnippetAreaMessage;
use Sulu\Snippet\Domain\Model\SnippetInterface;
use Sulu\Snippet\Domain\Repository\SnippetRepositoryInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

/**
 * Create snippets.
 *
 * @phpstan-import-type InputData from AbstractSuluContext
 */
class SuluSnippetContext extends AbstractSuluContext
{
    use HandleTrait;

    protected ?SnippetInterface $lastSnippet = null;

    public function __construct(
        EntityManagerInterface $entityManager,
        WebspaceManagerInterface $webspaceManager,
        MessageBusInterface $messageBus,
        protected SnippetRepositoryInterface $snippetRepository,
    ) {
        parent::__construct($entityManager, $webspaceManager);
        $this->messageBus = $messageBus;
    }

    #[BeforeScenario]
    public function reset(): void
    {
        $this->exec('DELETE FROM sn_snippet_dimension_contents');
        $this->exec('DELETE FROM sn_snippets');
    }

    #[Given('there is a(n) :template snippet')]
    public function thereIsASuluSnippet(string $template, ?TableNode $tableNode = null, ?PyStringNode $yaml = null): void
    {
        $data = $this->getData($tableNode, $yaml);
        $data = $this->applyDefaults($data);
        $data['template'] = $template;
        $snippet = $this->createSnippet($data);

        if ('publish' === $data['_action']) {
            $this->publishSnippet($snippet);
        }
        $this->lastSnippet = $snippet;
    }

    #[Given('the snippet is set as default for :area')]
    public function theSnippetIsSetAsDefaultFor(string $area, ?TableNode $tableNode = null): void
    {
        Assert::isInstanceOf($this->lastSnippet, SnippetInterface::class);
        /** @var array<string,string> $additional */
        $additional = $tableNode?->getRowsHash() ?? [];
        $webspace = $additional['_webspaceKey'] ?? $this->getWebspaceKey();
        $locale = $additional['locale'] ?? $this->getDefaultLocale($webspace);
        $this->setArea($area, webspaceKey: $webspace, locale: $locale);
    }

    /** @param InputData $data */
    protected function createSnippet(array $data): SnippetInterface
    {
        $message = new CreateSnippetMessage($data);
        $snippet = $this->handle(new Envelope($message, [new EnableFlushStamp()]));
        Assert::isInstanceOf($snippet, SnippetInterface::class);

        return $snippet;
    }

    protected function publishSnippet(SnippetInterface $snippet, ?string $locale = null): void
    {
        $locale ??= $this->getDefaultLocale();
        $message = new ApplyWorkflowTransitionSnippetMessage(['uuid' => $snippet->getUuid()], $locale, 'publish');
        /* @see \Sulu\Snippet\Application\MessageHandler\ApplyWorkflowTransitionSnippetMessageHandler */
        $this->handle(new Envelope($message, [new EnableFlushStamp()]));
    }

    public function setArea(string $area, ?SnippetInterface $snippet = null, ?string $webspaceKey = null, ?string $locale = null): void
    {
        $snippet ??= $this->lastSnippet;
        Assert::isInstanceOf($snippet, SnippetInterface::class);
        $webspaceKey ??= $this->getWebspaceKey();
        $locale ??= $this->getDefaultLocale($webspaceKey);
        $data = [
            'webspaceKey' => $webspaceKey,
            'snippetIdentifier' => ['uuid' => $snippet->getUuid()],
            'areaKey' => $area,
            'locale' => $locale,
        ];
        $message = new ModifySnippetAreaMessage($data);
        /* @see \Sulu\Snippet\Application\MessageHandler\ModifySnippetAreaMessageHandler */
        $this->handle(new Envelope($message, [new EnableFlushStamp()]));
    }

    /**
     * @param InputData $data
     *
     * @return InputData
     */
    protected function applyDefaults(array $data): array
    {
        $data['_action'] ??= 'publish';
        $data['title'] ??= 'new-snippet';
        $data['locale'] ??= $this->getDefaultLocale();

        return $data;
    }
}
