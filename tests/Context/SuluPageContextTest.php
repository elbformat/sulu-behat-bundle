<?php

namespace Context;

use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use Elbformat\SuluBehatBundle\Context\SuluPageContext;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\PageBundle\Document\PageDocument;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Sulu\Component\Webspace\Manager\WebspaceCollection;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\Webspace;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class SuluPageContextTest extends TestCase
{
    protected ?SuluPageContext $pageContext;
    protected ?EntityManagerInterface $em;
    protected ?DocumentManagerInterface $docManager;
    protected ?FormFactoryInterface $formFactory;
    protected ?WebspaceManagerInterface $webspaceManager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->docManager = $this->createMock(DocumentManagerInterface::class);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->webspaceManager = $this->createMock(WebspaceManagerInterface::class);
        $this->pageContext = new SuluPageContext($this->em, $this->docManager, $this->formFactory, $this->webspaceManager);
    }

    public function testThereIsAPage(): void
    {
        $page = new PageDocument();
        $this->docManager->method('create')->willReturn($page);
        $webspace = new Webspace();
        $webspaceCollection = new WebspaceCollection();
        $webspaceCollection->setWebspaces(['default' => $webspace]);
        $this->webspaceManager->method('getWebspaceCollection')->willReturn($webspaceCollection);
        $this->webspaceManager->method('findWebspaceByKey')->with('default')->willReturn($webspace);
        $form = $this->createMock(FormInterface::class);
        $form->method('submit')->willReturn(null);
        $this->formFactory->method('create')->willReturn($form);
        $tableData = [
            0 => ['title' => 'test'],
            1 => ['url' => '/test'],
        ];
        $this->pageContext->thereIsAPage(new TableNode($tableData));
    }
}