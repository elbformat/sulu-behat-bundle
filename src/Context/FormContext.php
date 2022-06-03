<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use DOMElement;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Form;

/**
 * Form handling is quite complex, so it has its own context (which is using the BrowserContext)
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
final class FormContext implements Context
{
    use DomElementNotFoundTrait;

    protected ?BrowserContext $browserContext = null;
    protected ?Form $lastForm = null;
    protected ?Crawler $lastFormCrawler = null;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $environment = $scope->getEnvironment();
        $this->browserContext = $environment->getContext(BrowserContext::class);
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
        $this->lastFormCrawler = $form->first();
    }

    /**
     * @When I fill :value into :name
     * @When I select :name radio button with value :value
     */
    public function iFillIntoInput(string $value, string $name): void
    {
        $this->getLastForm()->get($name)
            ->setValue($value);
    }

    /**
     * @When I check :name checkbox
     */
    public function iCheckCheckbox(string $name): void
    {
        /** @var ChoiceFormField $cb */
        $cb = $this->getLastForm()->get($name);
        $value = $cb->availableOptionValues()[0];
        $cb->setValue($value);
    }

    /**
     * @When I select :value from :name
     */
    public function iSelectFrom(string $value, string $name): void
    {
        /** @var ChoiceFormField $select */
        $select = $this->getLastForm()->get($name);
        $refl = new \ReflectionProperty(ChoiceFormField::class, 'node');
        $refl->setAccessible(true);
        /** @var DOMElement $node */
        $node = $refl->getValue($select);
        foreach ($node->childNodes as $child) {
            if ($child->textContent === $value) {
                $val = $child->getAttribute('value');
                $select->select($val);

                return;
            }
        }
        throw new \Exception('option not found');
    }

    /**
     * @When I submit the form( with extra data)
     */
    public function iSubmitTheForm(TableNode $table = null): void
    {
        if (null !== $table) {
            // Convert array to deep structure
            parse_str(http_build_query($table->getRowsHash()), $extraData);
        }
        $this->browserContext->submit($this->getLastForm(), $extraData ?? []);
    }

    /**
     * @Then the form must contain an input field
     */
    public function theFormMustContainAnInputField(TableNode $attribs): void
    {
        $inputs = $this->getLastFormCrawler()
            ->filterXPath('//input');

        /** @var DOMElement $input */
        foreach ($inputs as $input) {
            foreach ($attribs->getRowsHash() as $attrName => $attrVal) {
                if ($input->getAttribute($attrName) !== $attrVal) {
                    continue 2;
                }
            }

            return;
        }

        throw $this->createNotFoundException('input', $inputs);
    }

    protected function getLastFormCrawler(): Crawler
    {
        if (null === $this->lastFormCrawler) {
            throw new \DomainException('No form to refer to.');
        }

        return $this->lastFormCrawler;
    }

    protected function getLastForm(): Form
    {
        if (null === $this->lastForm) {
            throw new \DomainException('No form to refer to.');
        }

        return $this->lastForm;
    }
}
