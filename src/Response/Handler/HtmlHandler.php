<?php

namespace LAG\SmokerBundle\Response\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Goutte\Client;
use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Url\Registry\UrlProviderRegistry;
use LAG\SmokerBundle\Url\Requirements\Mapping\MappingResolverInterface;
use Symfony\Component\BrowserKit\Response;
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

    /**
     * @var UrlProviderRegistry
     */
    private $urlProviderRegistry;

    public function __construct(
        MappingResolverInterface $mappingResolver,
        EntityManagerInterface $entityManager,
        UrlProviderRegistry $urlProviderRegistry,
        array $configuration = []
    ) {
        parent::__construct($configuration);

        $this->mappingResolver = $mappingResolver;
        $this->entityManager = $entityManager;
        $this->configuration = $configuration;
        $this->urlProviderRegistry = $urlProviderRegistry;
    }

    public function handle(string $routeName, Crawler $crawler, Client $client, array $options = []): void
    {
        $configuration = $this->getConfiguration($routeName);
        $mapping = $this->mappingResolver->resolve($this->getMappingName($routeName), $routeName);

        foreach ($configuration as $selector => $content) {
            if ($this->isDynamicString($content)) {
                /** @var Response $response */
                $response = $client->getResponse();
                //$response->getHeader();



                var_dump($mapping, $client->getResponse());
                die;
//                $provider = $this->registry->get('default');
//                $provider->getRequirements($routeName, [
//                    'where' => '',
//                ]);
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
