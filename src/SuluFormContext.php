<?php declare(strict_types=1);

namespace Elbformat\SuluBehatBundle;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\FormBundle\Manager\FormManager;
use Sulu\Bundle\PageBundle\Document\PageDocument;
use Sulu\Bundle\PageBundle\Form\Type\PageDocumentType;

/**
 * Creating and testing sulu forms.
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
final class SuluFormContext extends DatabaseContext
{
    protected FormManager $formManager;

    public function __construct(EntityManagerInterface $em, FormManager $formManager)
    {
        parent::__construct($em);
        $this->formManager = $formManager;
    }

    /**
     * Clear all form contents before each scenario
     *
     * @BeforeScenario @form
     */
    public function resetDatabase(): void
    {
        $this->exec('DELETE FROM fo_forms');
        $this->exec('ALTER TABLE fo_forms AUTO_INCREMENT=1');
    }

    /**
     * @Given there is a(n) sulu form
     */
    public function thereIsASuluForm(TableNode $tableNode): void
    {
        $this->formManager->save($tableNode->getRowsHash(),'de');
    }

    protected function exec(string $query)
    {
        $this->em->getConnection()->query($query)->execute();
    }
}
