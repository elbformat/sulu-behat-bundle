<?php declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

/**
 * Form handling is quite complex, so it has its own context (which is using the BrowserContext)
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
final class FormContext implements Context
{
    protected BrowserContext $browserContext;

    protected Form $lastForm;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $environment = $scope->getEnvironment();
        $this->browserContext = $environment->getContext(BrowserContext::class);
    }

    /**
     * @When I submit the form( with extra data)
     */
    public function iSubmitTheForm(TableNode $table = null)
    {
        if (null !== $table) {
            // Convert array to deep structure
            parse_str(http_build_query($table->getRowsHash()), $extraData);
        }
        $this->browserContext->submit($this->lastForm, $extraData ?? []);
    }

    /**
     * @Then the page must contain a form named :name
     * @When I use form :name
     */
    public function thePageMustContainAFormNamed($name = null): void
    {
        $crawler = $this->browserContext->getCrawler();
        if (null !== $name) {
            $form = $crawler->filter(sprintf('form[name="%s"]', $name));
        } else {
            $form = $crawler->filter('form');
        }
        if (!$form->count()) {
            $formFounds = $crawler->filterXPath('//form');
            if ($formFounds->count()) {
                $names = [];
                foreach ($formFounds as $found) {
                    $names[] = $found->getAttribute('name');
                }
                throw new \DomainException(sprintf('Form not found. Did you mean one of "%s"?', implode('" or "', $names)));
            }
            throw new \DomainException('No form not found');
        }
        $this->lastForm = $form->form();
    }
}
