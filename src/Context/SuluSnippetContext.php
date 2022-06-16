<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\SnippetBundle\Document\SnippetDocument;
use Sulu\Bundle\SnippetBundle\Form\SnippetType;
use Sulu\Bundle\SnippetBundle\Snippet\DefaultSnippetManagerInterface;
use Sulu\Component\Content\Compat\Structure;
use Sulu\Component\Content\Document\WorkflowStage;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Creating and testing sulu snippets.
 *
 * @author Jens Stapelfeldt <jst@elbformat.de>
 */
final class SuluSnippetContext extends AbstractPhpCrContext
{
    protected ?SnippetDocument $lastDocument = null;
    protected DefaultSnippetManagerInterface $defaultSnippetManager;

    public function __construct(EntityManagerInterface $em, DocumentManagerInterface $docManager, FormFactoryInterface $formFactory, WebspaceManagerInterface $webspaceManager, DefaultSnippetManagerInterface $defaultSnippetManager)
    {
        parent::__construct($em, $webspaceManager, $docManager, $formFactory);
        $this->defaultSnippetManager = $defaultSnippetManager;
    }

    /**
     * @Given there is a(n) :template snippet
     */
    public function thereIsASuluSnippet(TableNode $tableNode, string $template): void
    {
        /** @var SnippetDocument $document */
        $document = $this->docManager->create(Structure::TYPE_SNIPPET);

        $data = $tableNode->getRowsHash();
        $data['template'] = $template;

        $document->setWorkflowStage(WorkflowStage::PUBLISHED);
        $this->saveDocument($document, $this->expandData($data), SnippetType::class);

        $this->lastDocument = $document;
    }

    /**
     * @Given the snippet is set as default for :area
     */
    public function theSnippetIsSetAsDefaultFor(string $area): void
    {
        $this->defaultSnippetManager->save(
            $this->getWebspaceKey(),
            $area,
            $this->getLastDocument()->getUuid(),
            $this->getLocale(),
        );
    }

    protected function getLastDocument(): SnippetDocument
    {
        if (null === $this->lastDocument) {
            throw new \DomainException('No snippet queried.');
        }

        return $this->lastDocument;
    }
}
