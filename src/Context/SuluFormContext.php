<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Doctrine\ORM\EntityManagerInterface;
use Elbformat\SymfonyBehatBundle\Context\NestedMapTrait;
use Sulu\Bundle\FormBundle\Controller\FormController;
use Sulu\Bundle\FormBundle\Entity\Form;
use Sulu\Bundle\FormBundle\Manager\FormManager;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Webmozart\Assert\Assert;

/**
 * Creating and testing sulu forms. Requires sulu/form-bundle to be installed.
 *
 * @phpstan-import-type NestedMap from NestedMapTrait
 */
class SuluFormContext extends AbstractSuluContext
{
    protected ?Form $lastForm = null;

    public function __construct(
        EntityManagerInterface $entityManager,
        WebspaceManagerInterface $webspaceManager,
        protected FormManager $formManager,
    ) {
        parent::__construct($entityManager, $webspaceManager);
    }

    #[BeforeScenario]
    public function resetDatabase(): void
    {
        $this->exec('DELETE FROM fo_forms');
        $this->exec('ALTER TABLE fo_forms AUTO_INCREMENT=1000');
    }

    #[Given('there is a(n) sulu form')]
    public function thereIsASuluForm(TableNode $tableNode): void
    {
        $data = $this->getDataFromTableOrString($tableNode);
        Assert::string($data['locale']);
        $this->lastForm = $this->formManager->save($data, $data['locale'] ?? $this->getDefaultLocale());
    }

    #[Given('the form contains a(n) :type field')]
    public function theFormContainsAField(string $type, ?TableNode $tableNode = null): void
    {
        $data = $this->getLastFormData();
        $fieldData = $this->getDataFromTableOrString($tableNode);
        $fieldData['type'] = $type;
        Assert::string($fieldData['locale']);
        if (!isset($data['fields']) || !\is_array($data['fields'])) {
            $data['fields'] = [];
        }
        $data['fields'][] = $fieldData;

        Assert::isInstanceOf($this->lastForm, Form::class);
        $this->lastForm = $this->formManager->save($data, $fieldData['locale'] ?? $this->getDefaultLocale(), $this->lastForm->getId());
    }

    /**
     * @return NestedMap
     */
    protected function getLastFormData(): array
    {
        // we need to call a private method in a static way to not copy&paste 80 lines of code
        $controller = new \ReflectionClass(FormController::class);
        $cont = $controller->newInstanceWithoutConstructor();
        $method = $controller->getMethod('getApiEntity');
        $method->setAccessible(true);
        Assert::isInstanceOf($this->lastForm, Form::class);
        $data = $method->invoke($cont, $this->lastForm, $this->getDefaultLocale());
        self::assertNestedMap($data);

        return $data;
    }
}
