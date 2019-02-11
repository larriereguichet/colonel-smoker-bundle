# Colonel Smoker Bundle
[![Build Status](https://travis-ci.org/larriereguichet/colonel-smoker-bundle.svg?branch=master)](https://travis-ci.org/larriereguichet/colonel-smoker-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/larriereguichet/colonel-smoker-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/larriereguichet/colonel-smoker-bundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/larriereguichet/colonel-smoker-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/larriereguichet/colonel-smoker-bundle/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/lag/colonel-smoker-bundle/v/stable)](https://packagist.org/packages/lag/colonel-smoker-bundle)
[![License](https://poser.pugx.org/lag/colonel-smoker-bundle/license)](https://packagist.org/packages/lag/colonel-smoker-bundle)

![logo](https://vignette.wikia.nocookie.net/onepiece/images/1/1c/Smoker_Anime_Pre_Timeskip_Infobox.png/revision/latest/scale-to-width-down/150)

The **Colonel Smoker** is an expert in smoke. He can even tell if your application is smoking before deploying it to production.

The **Colonel Smoker** find the URLs of your Symfony application (only Symfony is supported now) and looks for `500` errors. 
- helps providing requirements parameters for your dynamic URLs
- uses the routes declared in the Symfony routing by default

> This bundle relies on Symfony service injection. If your are not familiar with this, you can read documentation [here](https://symfony.com/doc/current/service_container.html)

## Installation

```bash
composer require --dev lag/colonel-smoker-bundle:dev-master
```

## Configuration
```yaml
# config/packages/dev/lag_smoker.yaml
lag_smoker:
    host: 'http://127.0.0.1:8000/' # This is the default configuration
    # This route will be used to generated urls to test against
    routes:
        # The homepage route has no parameters and expect a 200 OK response code
        app.homepage: ~
        # The show article requires parameters. They will be provided by the mapping "article"
        app.show_article:
            mapping: article
    # The mapping will be used to generated urls with dynamic parameters
    mapping:
        # This name can be anything, but it has to be unique
        article:
            # This mapping will be used with the Article entity of your application
            entity: App\Entity\Article
            # The property id in the route parameters (/articles/{id} for example) will be mapped with id property of your entity
            requirements:
                id: id
```

## Usage
1. First generate the URL cache :
```bash
bin/console smoker:generate-cache
```

2. Then run the smoke tests :
```bash
bin/console smoker:smoke
```

> If you use the Symfony WebServer bundle, dont forget to run `bin/console server:start`.

## Reference Configuration
```yaml
lag_smoker:
    routes:
        app.homepage:
            mapping: null
            provider: symfony
            handlers:
                response_code: 200
        app.show_articles_redirection:
            mapping: article
            provider: my_custom_requirements_provider
            handlers:
                response_code: 302
     mapping:
        article:
            entity: App\Entity\Article
            provider: default
            pattern: 'app.article_'
            excludes:
                - app.article.excluded_routes
            requirements:
                id: id
                categorySlug: category.slug
```
## URL Providers
### Symfony provider
The **Colonel Smoker** provides a built-in provider named 'symfony' which use the Symfony routing of your application 
to build urls.

However you can create your own provider. 

### Custom provider
You can create a custom url provider by declaring a service which implements `LAG\SmokerBundle\Url\Provider\UrlProviderInterface`.

```php
class MyCustomUrlProvider implements UrlProviderInterface
{
    public function getCollection(array $options = []): UrlCollection
    {
        $collection = new UrlCollection();
        
        // Fill the collection with your urls
        
        return $collection;
    }

    public function match(string $path): string
    {
        // The provider should be able to match an url and give the route name
        $routeName = '...';
        
        return $routeName;
    }

    public function supports(string $path): bool
    {
        // The provider should be able to say if the given path is supported
        return $path === '/articles";
    }

    public function configure(OptionsResolver $resolver)
    {
    }

    public function getName(): string
    {
        return 'my_little_provider';
    }
}
```
Then configure your routes to use it:
```yaml
lag_smoker:
    # ...
    app.homepage:
        provider: symfony # this is the default configuration
    my_weird_route:
        provider: my_little_provider
```
 
## Handle dynamic URLs
Most of the urls of a website are dynamic. They require dynamic parameters to be build. The **Colonel Smoker** has a 
built-in requirements provider using Doctrine to find parameter values. This is useful as in most Symfony's application,
urls are build using data from the database.

### Doctrine provider
The **Colonel Smoker** comes with a built-in requirements providers for routes. It use the Doctrine ORM to find data to
fill urls dynamic values. It can also handle static values.

The mapping between database data and urls parameters can be configured in the `mapping` section as following :
```yaml
lag_smoker:
     mapping:
        # A unique name
        article:
            # It should the fully-qualified class name of a valid entity            
            entity: App\Entity\Article
            # This is the default value, you can omit it 
            provider: default
            # The pattern for the routes related to the entity. This example will take all routes with a name containing app.article_ 
            pattern: 'app.article_'
            # But it will excludes this specific routes
            excludes:
                - app.article.excluded_route
            # Requirements are mandatory
            requirements:
                # This will map the "id" parameters contained in routes with the id property of the entity
                # To get the value, it will search for getId() or public property id
                id: id
                # It can also take a value from a linked entity, for example the slug of the article category
                categorySlug: category.slug
                slug: slug
                # This value is static, it should be preceded by @
                version: '@v1'
```
This configuration will map all routes with a name containing the pattern `app.article` but the route `app.article.excluded_route`.
The **Colonel Smoker** will use the Doctrine ORM to find all `Article` entities, and get an url for each of them.

Parameters required for some routes will be filled with values found using the given mapping in the requirements section
of the configuration.

For example, the route "app.article_show" which is defined with `/articles/{id}` will be generated for each Article in
your database, and the `id` parameters will be mapped with the getId() method of your entities. For the route "app.articles_by_category" 
(`/{categorySlug}/{slug}) will be mapped by the getCategory()->getSlug() methods (or public properties) and the slug 
parameters by the getSlug() method of the article entity.

You can also pass static values for some reason. To do this, just add `@` before the value. 

### Custom provider
You can use a custom requirements provider by declaring a service which implements `\LAG\SmokerBundle\Url\Requirements\Provider\RequirementsProviderInterface`.

```php
   
class MyRequirementsProvider implements RequirementsProviderInterface 
{   
    public function getName(): string
    {
        return 'my_provider';
    }

    public function supports(string $routeName): bool
    {
        return 'my_weird_route' === $routeName;
    }

    public function getRequirements(string $routeName, array $options = []): Traversable
    {
        // ...
        foreach ($things as $stuff) {
            $parameters = [
                'id' => $stuff->getId(),
            ];
            yield $parameters;       
       }
    }
```

## Response handlers
The **Colonel Smoker** use response handlers to test if the given response is valid to pass the test. It comes with a
built-in handlers checking response status code. You can also add your own handlers to test the returned content for 
example.

### Status code response handler
Most of the time, an url should respond a 200 OK status code. This is the default configuration. But sometimes, you may 
want expect different values to be valid. 302 can be a valid value. We can also want to test the 500 exception page.

In this case you can configure the expected response code in the route configuration :
```yaml
lag_smoker:
    routes:
        route_with_redirection:
            handlers:
                response_code: 302
        route_with_error:
            handlers:
                response_code: 500
```

### Custom response handler
You can also add your custom response handler by creating a service which implements 
`LAG\SmokerBundle\Response\Handler\ResponseHandlerInterface`.

```php

class MyResponseHandler implements LAG\SmokerBundle\Response\Handler\ResponseHandlerInterface
{
    public function handle(string $routeName, Crawler $crawler, Client $client, array $options = []): void
    {
        // Check if the html contains the required code...
    ]
]
```

> The **Colonel** relies on the Symfony [DomCrawler](https://symfony.com/doc/current/components/dom_crawler.html) component and the [Goutte](https://github.com/FriendsOfPHP/Goutte) client.
