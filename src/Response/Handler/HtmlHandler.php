<?php

namespace LAG\SmokerBundle\Response\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Goutte\Client;
use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Url\Requirements\Mapping\MappingResolverInterface;
use Symfony\Component\DomCrawler\Crawler;

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

    public function __construct(
        MappingResolverInterface $mappingResolver,
        EntityManagerInterface $entityManager,
        array $configuration = []
    ) {
        parent::__construct($configuration);

        $this->mappingResolver = $mappingResolver;
        $this->entityManager = $entityManager;
        $this->configuration = $configuration;
    }

    public function handle(string $routeName, Crawler $crawler, Client $client, array $options = []): void
    {
        $configuration = $this->getConfiguration($routeName);
        $mapping = $this->mappingResolver->resolve($this->getMappingName($routeName), $routeName);

        foreach ($configuration as $selector => $content) {
            if ('{{' === substr($content, 0, 2) && '}}' === substr($content, -2)) {


//                $provider = $this->registry->get('default');
//                $provider->getRequirements($routeName, [
//                    'where' => '',
//                ]);
            }

            if (false === strpos($crawler->filter($selector)->text(), $content)) {
                throw new Exception();
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
}
