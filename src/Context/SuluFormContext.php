<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\FormBundle\Controller\FormController;
use Sulu\Bundle\FormBundle\Entity\Form;
use Sulu\Bundle\FormBundle\Manager\FormManager;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

/**
 * Creating and testing sulu forms.
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
class SuluFormContext extends AbstractSuluContext
{
    protected FormManager $formManager;
    protected ?Form $lastForm = null;

    public function __construct(EntityManagerInterface $em, WebspaceManagerInterface $webspaceManager, FormManager $formManager)
    {
        parent::__construct($em, $webspaceManager);
        $this->formManager = $formManager;
    }

    /**
     * Clear all form contents before each scenario
     *
     * @BeforeScenario
     */
    public function resetDatabase(): void
    {
        $this->exec('DELETE FROM fo_forms');
        $this->exec('ALTER TABLE fo_forms AUTO_INCREMENT=1000');
    }

    /**
     * @Given there is a(n) sulu form
     */
    public function thereIsASuluForm(TableNode $tableNode): void
    {
        /** @var array<string,string> $tableData */
        $tableData = $tableNode->getRowsHash();
        $this->lastForm = $this->formManager->save($this->expandData($tableData), $this->getLocale());
    }

    /**
     * @Given the form contains a(n) :type field
     */
    public function theFormContainsAField(string $type, TableNode $tableNode = null): void
    {
        $data = $this->getLastFormData();
        if (null !== $tableNode) {
            /** @var array<string,string> $tableData */
            $tableData = $tableNode->getRowsHash();
            $fieldData = $this->expandData($tableData);
        } else {
            $fieldData = [];
        }
        $fieldData['type'] = $type;
        if (!isset($data['fields']) || !is_array($data['fields'])) {
            $data['fields'] = [];
        }
        $data['fields'][] = $fieldData;

        $this->lastForm = $this->formManager->save($data, $this->getLocale(), $this->getLastForm()->getId());
    }

    protected function getLastForm(): Form
    {
        if (null === $this->lastForm) {
            throw new \DomainException('No form selected');
        }

        return $this->lastForm;
    }

    /** @return mixed[] */
    protected function getLastFormData(): array
    {
        // we need to call a private method in a static way to not copy&paste 80 lines of code
        $controller = new \ReflectionClass(FormController::class);
        $cont = $controller->newInstanceWithoutConstructor();
        $method = $controller->getMethod('getApiEntity');
        $method->setAccessible(true);

        /** @var mixed[] */
        return $method->invoke($cont, $this->getLastForm(), $this->getLocale());
    }
}
