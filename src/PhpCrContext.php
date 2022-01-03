<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\ArticleBundle\Document\ArticleDocument;
use Sulu\Bundle\PageBundle\Document\PageDocument;
use Sulu\Bundle\PageBundle\Form\Type\PageDocumentType;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Handles internal phpcr parts. Used by Sulu Content and Articles.
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
abstract class PhpCrContext extends DatabaseContext
{
    protected const WEBSPACE = 'warnermusic';
    protected const LOCALE = 'de';

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var DocumentManagerInterface */
    protected $docManager;

    /** @var array<int,string> */
    private static $documentIdStack = [];

    public function __construct(EntityManagerInterface $em, DocumentManagerInterface $docManager, FormFactoryInterface $formFactory)
    {
        parent::__construct($em);
        $this->docManager = $docManager;
        $this->formFactory = $formFactory;
    }

    /**
     * @BeforeScenario @sulu
     */
    public function resetDocumentIdStack(): void
    {
        self::$documentIdStack = [];
    }

    /** @param ArticleDocument|PageDocument $document */
    protected function saveDocument($document, array $data, string $formType = PageDocumentType::class, ?string $webSpaceKey = null)
    {
        // Bind data to form
        $initialData = [
            // disable csrf protection, since we can't produce a token, because the form is cached on the client
            'csrf_protection' => false,
        ];
        if (null !== $webSpaceKey) {
            $initialData['webspace_key'] = $webSpaceKey;
        }
        $form = $this->formFactory->create($formType, $document, $initialData);
        $form->submit($data, false);

        // Save updated document
        $persistOptions = [
            'clear_missing_content' => false,
        ];
        $this->docManager->persist($document, self::LOCALE, $persistOptions);
        $this->docManager->publish($document, self::LOCALE);
        $this->docManager->flush();
        self::$documentIdStack[] = $document->getUuid();
    }

    /** Convert data with do notation into nested array structure */
    protected function expandData(array $data): array
    {
        $newData = [];
        foreach ($data as $k => $v) {
            // Plain key
            if (false === strpos((string) $k, '.')) {
                $newData[$k] = $this->replacePlaceholders($v);
                continue;
            }
            $parts = explode('.', $k, 2);
            $deepStructure = $this->expandData([$parts[1] => $v]);
            $newData[$parts[0]] = array_merge_recursive($newData[$parts[0]] ?? [],$deepStructure);
        }

        return $newData;
    }

    protected function replacePlaceholders(string $value): string
    {
        if (preg_match('/\[DOCUMENT_ID\[(\d+)]]/',$value, $match)) {
            $value = preg_replace('/\[DOCUMENT_ID\[(\d+)]]/', self::$documentIdStack[$match[1]], $value);
        }

        return $value;
    }

    protected function isJson(string $module, string $field): bool
    {
        switch ($module . '/' . $field) {
            case 'events/events':
            //case 'events/artists':
            case 'songs/songs':
            case 'songs/artists':
            case 'stage/block_link':
                return true;
            default:
                return false;
        }
    }
}
