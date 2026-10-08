<?php

declare(strict_types=1);

namespace Context;

use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use Elbformat\SuluBehatBundle\Context\SuluPageContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Manager\WebspaceCollection;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\Webspace;
use Sulu\Page\Application\Message\ApplyWorkflowTransitionPageMessage;
use Sulu\Page\Application\Message\CreatePageMessage;
use Sulu\Page\Domain\Model\Page;
use Sulu\Page\Domain\Repository\PageRepositoryInterface;
use Sulu\Route\Domain\Repository\RouteRepositoryInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class SuluPageContextTest extends TestCase
{
    protected SuluPageContext $pageContext;
    protected EntityManagerInterface&MockObject $em;
    protected WebspaceManagerInterface&MockObject $webspaceManager;
    protected MessageBusInterface&MockObject $messageBus;
    protected PageRepositoryInterface&MockObject $pageRepository;
    protected RouteRepositoryInterface&MockObject $routeRepository;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->webspaceManager = $this->createMock(WebspaceManagerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->pageRepository = $this->createMock(PageRepositoryInterface::class);
        $this->routeRepository = $this->createMock(RouteRepositoryInterface::class);
        $this->pageContext = new SuluPageContext(
            $this->em,
            $this->webspaceManager,
            $this->messageBus,
            $this->pageRepository,
            $this->routeRepository
        );
    }

    public function testThereIsAPage(): void
    {
        $webspace = new Webspace();
        $webspace->setDefaultLocalization(new Localization('en', 'GB'));
        $webspaceCollection = new WebspaceCollection();
        $webspaceCollection->setWebspaces(['default' => $webspace]);
        $this->webspaceManager->method('getWebspaceCollection')->willReturn($webspaceCollection);
        $this->webspaceManager->method('findWebspaceByKey')->with('default')->willReturn($webspace);
        $this->pageRepository->method('getOneBy')->with(
            ['webspaceKey' => 'default', 'parentId' => null], ['uuid' => 'uuid'])->willReturn(new Page('parent'));

        $newPage = new Page('abcdef');
        $newPage->setWebspaceKey('default');
        $messages = [];
        $this->messageBus->method('dispatch')->willReturnCallback(static function (object $message) use ($newPage, &$messages) {
            $messages[] = $message;

            return new Envelope($message, [new HandledStamp($newPage, 'mock')]);
        });

        $tableData = [
            ['title', 'test'],
            ['url', '/test'],
        ];
        $this->pageContext->thereIsAPage(new TableNode($tableData));
        $this->assertCount(2, $messages);
        $this->assertInstanceOf(Envelope::class, $messages[0]);
        $this->assertInstanceOf(Envelope::class, $messages[1]);

        $createMsg = $messages[0]->getMessage();
        $this->assertInstanceOf(CreatePageMessage::class, $createMsg);
        $this->assertSame('test', $createMsg->getData()['title']);
        $this->assertSame('/test', $createMsg->getData()['url']);

        $publishMsg = $messages[1]->getMessage();
        $this->assertInstanceOf(ApplyWorkflowTransitionPageMessage::class, $publishMsg);
        $this->assertSame('publish', $publishMsg->getTransitionName());
    }
}
