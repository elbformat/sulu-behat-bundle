<?php declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\SnippetBundle\Document\SnippetDocument;
use Sulu\Bundle\SnippetBundle\Form\SnippetType;
use Sulu\Bundle\SnippetBundle\Snippet\DefaultSnippetManagerInterface;
use Sulu\Component\Content\Compat\Structure;
use Sulu\Component\Content\Document\WorkflowStage;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Creating and testing sulu snippets.
 *
 * @author Jens Stapelfeldt <jst@elbformat.de>
 */
final class SuluSnippetContext extends PhpCrContext
{
    protected ?SnippetDocument $lastDocument = null;
    protected DefaultSnippetManagerInterface $defaultSnippetManager;

    public function __construct(EntityManagerInterface $em, DocumentManagerInterface $docManager, FormFactoryInterface $formFactory, DefaultSnippetManagerInterface $defaultSnippetManager)
    {
        parent::__construct($em, $docManager, $formFactory);
        $this->defaultSnippetManager = $defaultSnippetManager;
    }

    /**
     * @Given there is a(n) sulu snippet
     */
    public function thereIsASuluSnippet(TableNode $tableNode): void
    {
        /** @var SnippetDocument $document */
        $document = $this->docManager->create(Structure::TYPE_SNIPPET);

        $data = $tableNode->getRowsHash();

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
            $this->lastDocument->getUuid(),
            $this->getLocale(),
        );
    }

    protected function getLastDocument(): object
    {
        if (null === $this->lastDocument) {
            throw new \DomainException('No snippet queried.');
        }

        return $this->lastDocument;
    }


}
