<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use DOMAttr;
use DOMElement;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This Context is adapted from Mink Context but with less overhead by using the Kernel directly.
 * See https://github.com/Behat/MinkExtension/blob/master/src/Behat/MinkExtension/Context/MinkContext.php for more
 * methods
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
final class BrowserContext implements Context
{
    private KernelInterface $kernel;

    private ?Response $response;

    private ?Request $request;

    /** @var array */
    private $cookies = [];

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given I am logged in as admin
     */
    public function iAmLoggedInAsAdmin()
    {
        $jsonData = json_encode(['username' => 'admin', 'password' => 'admin'], JSON_THROW_ON_ERROR);
        $server = ['CONTENT_TYPE' => 'application/json'];
        $this->doRequest(Request::create('/admin/login', 'POST', [], [], [], $server, $jsonData));
    }

    /**
     * Opens specified page
     * Example: Given I am on "http://batman.com"
     * Example: And I am on "/articles/isBatmanBruceWayne"
     * Example: When I go to "/articles/isBatmanBruceWayne"
     *
     * @Given /^(?:|I )am on "(?P<page>[^"]+)"$/
     * @When /^(?:|I )go to "(?P<page>[^"]+)"$/
     */
    public function visit(string $page): void
    {
        $this->doRequest(Request::create($page, 'GET', [], $this->cookies));
    }

    /**
     * @When I send a :method request to :url
     */
    public function sendARequestTo(string $method, string $url, ?PyStringNode $data = null): void
    {
        if ($data) {
            $server = ['CONTENT_TYPE' => 'application/json'];
        }
        $this->doRequest(Request::create($url, strtoupper($method), [], $this->cookies, [], $server ?? [], $data ? $data->getRaw() : null));
    }

    /**
     * @When I follow the redirect
     */
    public function iFollowtheRedirect(): void
    {
        if (null === $this->response) {
            throw new \DomainException('No request was made yet');
        }
        $code = $this->response->getStatusCode();
        if ($code >= 400 || $code < 300) {
            throw new \DomainException('No redirect code found: Code ' . $code);
        }
        $targetUrl = $this->response->headers->get('Location');
        // This is not url, not even a path. Not RFC compliant but we need to handle it either way
        if (0 === strpos($targetUrl, '?')) {
            $targetUrl = $this->request->getUri() . $targetUrl;
        }
        $this->doRequest(Request::create($targetUrl, 'GET', [], $this->cookies));
    }

    public function submit(Form $form, array $extraData = []): void
    {
        $form->setValues($extraData);
        $this->doRequest(Request::create($form->getUri(), $form->getMethod(), $form->getPhpValues(), $this->cookies));
    }

    /**
     * Checks, that current page response status is equal to specified
     * Example: Then the response status code should be 200
     * Example: And the response status code should be 400
     *
     * @Then /^the response status code should be (?P<code>\d+)$/
     */
    public function assertResponseStatus(string $code): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('No response received');
        }
        if ($this->response->getStatusCode() !== (int) $code) {
            throw new \RuntimeException('Received ' . $this->response->getStatusCode());
        }
    }

    /**
     * Checks, that page contains specified text
     * Example: Then I should see "Who is the Batman?"
     * Example: And I should see "Who is the Batman?"
     *
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function assertPageContainsText(string $text)
    {
        $regex = '/' . preg_quote($text, '/') . '/ui';
        $actual = $this->response->getContent();
        if (!preg_match($regex, $actual)) {
            throw new \DomainException('Text not found');
        }
    }

    /**
     * @Then /^(?:|I )should not see "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function assertPageNotContainsText(string $text)
    {
        try {
            $this->assertPageContainsText($text);
        } catch (\DomainException $e) {
            return;
        }
        throw new \DomainException('Text found');
    }

    /**
     * @Then I should see a link to :url
     */
    public function iShouldSeeALinkTo(string $url)
    {
        $this->mustContainTag('a', ['href' => $url]);
    }

    /**
     * @Then I should see a(n) :tag tag
     */
    public function ishouldSeeATag(string $tag, ?TableNode $table = null, ?PyStringNode $content = null)
    {
        $this->mustContainTag($tag, $table ? $table->getRowsHash() : null, $content ? $content->getRaw() : null);
    }

    /**
     * @Then I should not see a(n) :tag tag
     */
    public function ishouldNotSeeATag(string $tag, ?TableNode $table = null, ?PyStringNode $content = null)
    {
        try {
            $this->mustContainTag($tag, $table ? $table->getRowsHash() : null, $content ? $content->getRaw() : null);
        } catch (\DomainException $e) {
            return;
        }
        throw new \Exception('Tag found');
    }

    public function getCrawler(): Crawler
    {
        if (null === $this->response) {
            throw new \DomainException('No request was made yet.');
        }

        return new Crawler($this->response->getContent(), $this->request->getUri());
    }

    protected function mustContainTag(string $tagName, ?array $attr = null, ?string $content = null): void
    {
        $crawler = new Crawler($this->response->getContent());
        $xPath = '//' . $tagName;
        if (null !== $attr) {
            foreach ($attr as $attrName => $attrVal) {
                $xPath .= sprintf('[@%s="%s"]', $attrName, $attrVal);
            }
        }
        $elements = $crawler->filterXPath($xPath);

        if (!$elements->count()) {
            $nearestTags = [];
            /** @var DOMElement $link */
            foreach ($crawler->filterXPath('//' . $tagName) as $nearMatch) {
                $attrs = [];
                /** @var DOMAttr $domAttr */
                foreach ($nearMatch->attributes as $domAttr) {
                    $attrs[] = sprintf('%s="%s"', $domAttr->name, $domAttr->value);
                }
                $nearestTags[] = sprintf('<%s %s>', $tagName, implode(' ', $attrs));
            }

            throw new \DomainException(sprintf("No matching %s tags found. Did you mean one of \n%s", $tagName, implode("\n", $nearestTags)));
        }

        // Check content
        if (null !== $content) {
            $content = trim($content);
            /** @var DOMElement $link */
            $foundContents = [];
            foreach ($elements as $link) {
                if ($content === trim($link->textContent)) {
                    return;
                }
                $foundContents[] = trim($link->textContent);
            }
            throw new \DomainException(sprintf("No matching content found for %s tag. Did you mean one of\n%s", $tagName, implode("\n", $foundContents)));
        }
    }

    protected function doRequest(Request $request): void
    {
        $this->request = $request;
        // Reboot kernel
        $this->kernel->shutdown();
        $this->response = $this->kernel->handle($request);
        foreach ($this->response->headers->getCookies() as $cookie) {
            $this->cookies[$cookie->getName()] = $cookie->getValue();
        }
    }
}
