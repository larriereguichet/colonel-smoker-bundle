# Colonel Smoker Bundle
[![Build Status](https://travis-ci.org/larriereguichet/colonel-smoker-bundle.svg?branch=master)](https://travis-ci.org/larriereguichet/colonel-smoker-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/larriereguichet/colonel-smoker-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/larriereguichet/colonel-smoker-bundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/larriereguichet/colonel-smoker-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/larriereguichet/colonel-smoker-bundle/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/lag/colonel-smoker-bundle/v/stable)](https://packagist.org/packages/lag/colonel-smoker-bundle)
[![License](https://poser.pugx.org/lag/colonel-smoker-bundle/license)](https://packagist.org/packages/lag/colonel-smoker-bundle)

![logo](https://vignette.wikia.nocookie.net/onepiece/images/1/1c/Smoker_Anime_Pre_Timeskip_Infobox.png/revision/latest/scale-to-width-down/150)

The **Colonel Smoker** is an expert in smoke. He can even tell if your application is smoking before deploying it to production.

The **Colonel Smoker** find the URLs of your Symfony application (only Symfony is supported now) and looks for `500` errors.

Main features : 
- helps providing requirements parameters for your dynamic URLs
- uses the routes declared in the Symfony routing by default
- check for response code
- check if the resulted html contains dynamic data from your entity

The goal is to ensure that each urls in your application does not contains critical errors. It is especially designed 
for application which have pages  with complex and dynamic data to display. 

> This bundle relies on Symfony service injection. If your are not familiar with this, you can read documentation [here](https://symfony.com/doc/current/service_container.html)

## Installation

```bash
composer require --dev lag/colonel-smoker-bundle
```

## Configuration
```yaml
# config/packages/test/lag_smoker.yaml
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
            entity: App\JK\CmsBundle\Entity\Article
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

> If you use the Symfony WebServer bundle, dont forget to run `bin/console server:start --env=test`.

## How it works
The **Colonel Smoker** will read the configuration and use the Symfony routing to build the urls of your application. 
Urls are stored in a cache. Then he calls each urls and analyze the response to find 500 errors. But it can tests more 
like if the html contains some static or dynamic values.

For example, if your application handles articles, the **Colonel Smoker** can check if the page displaying the article 
contains the article title. 

## Documentation
1. [Getting started](https://github.com/larriereguichet/colonel-smoker-bundle/tree/master/src/Resources/docs/1.GettingStarted.md)
2. [Build Urls](https://github.com/larriereguichet/colonel-smoker-bundle/tree/master/src/Resources/docs/2.BuildUrls.md)
2. [ResponseHandlers](https://github.com/larriereguichet/colonel-smoker-bundle/tree/master/src/Resources/docs/3.ResponseHandlers.md)

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
            entity: App\JK\CmsBundle\Entity\Article
            provider: symfony
            pattern: 'app.article_'
            excludes:
                - app.article.excluded_routes
            requirements:
                id: id
                categorySlug: category.slug
        
```

> The **Colonel** relies on the Symfony [DomCrawler](https://symfony.com/doc/current/components/dom_crawler.html) component and the [Goutte](https://github.com/FriendsOfPHP/Goutte) client.

## Known Issues
In dev environment, we call the Symfony Client to many times when having the server on the same machine (when using the 
Symfony web server for example), the cache miss to retrieve value and throws an exception. It causes some build failures.
A patch is in progress to avoid this.

## Road map

- [ ] Add an option to set a timeout in tests to avoid error with the cache
- [ ] Add a check to see if the web server is running
