<?php

namespace LAG\SmokerBundle\Response\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Goutte\Client;
use LAG\SmokerBundle\Contracts\Requirements\Mapping\MappingResolverInterface;
use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Url\Registry\UrlProviderRegistry;
use LAG\SmokerBundle\Url\Requirements\Registry\RequirementsProviderRegistry;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\PropertyAccess\PropertyAccess;

class HtmlHandler extends AbstractHandler
{
    /**
     * @var MappingResolverInterface
     */
    private $mappingResolver;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UrlProviderRegistry
     */
    private $urlProviderRegistry;
    /**
     * @var RequirementsProviderRegistry
     */
    private $requirementsProviderRegistry;

    public function __construct(
        MappingResolverInterface $mappingResolver,
        EntityManagerInterface $entityManager,
        UrlProviderRegistry $urlProviderRegistry,
        RequirementsProviderRegistry $requirementsProviderRegistry,
        array $configuration = []
    ) {
        parent::__construct($configuration);

        $this->mappingResolver = $mappingResolver;
        $this->entityManager = $entityManager;
        $this->configuration = $configuration;
        $this->urlProviderRegistry = $urlProviderRegistry;
        $this->requirementsProviderRegistry = $requirementsProviderRegistry;
    }

    public function handle(string $routeName, Crawler $crawler, Client $client, array $options = []): void
    {
        $configuration = $this->getConfiguration($routeName);
        $mapping = $this->mappingResolver->resolve($routeName);
        $accessor = PropertyAccess::createPropertyAccessor();

        if (null === $mapping) {
            throw new Exception('No mapping found for the html response handler for the route "'.$routeName.'"');
        }

        foreach ($configuration as $selector => $content) {
            preg_match('#\{\{(.*?)\}\}#', $content, $match);
            $isDynamicString = 1 < count($match);

            if ($isDynamicString) {
                $identifiers = $options['_identifiers'];

                foreach ($this->requirementsProviderRegistry->all() as $requirementsProvider) {
                    if (!$requirementsProvider->supports($routeName)) {
                        continue;
                    }

                    $entities = $requirementsProvider->getDataProvider()->getData($mapping['entity'], [
                        'where' => $identifiers,
                    ]);

                    foreach ($entities as $entity) {
                        $entity = $entity[0];
                        preg_match('#\{\{(.*?)\}\}#', $content, $match);
                        $property = trim($match[1]);
                        $valueToFind = $accessor->getValue($entity, $property);
                        $found = false;

                        $crawler->filter($selector)->each(function (Crawler $node) use ($valueToFind, &$found) {
                            if (false !== strpos($node->text(), $valueToFind)) {
                                $found = true;
                            }
                        });
                    }
                }
            } else {
                if (false === strpos($crawler->filter($selector)->text(), $content)) {
                    throw new Exception();
                }
            }
        }
    }

    /**
     * Return the unique name of the response handler.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'html';
    }

    protected function isDynamicString(string $content)
    {
        if ('{{' !== substr($content, 0, 2)) {
            return false;
        }

        if ('}}' !== substr($content, -2)) {
            return false;
        }

        return true;
    }
}
