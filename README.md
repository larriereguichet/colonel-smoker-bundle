# Colonel Smoker Bundle

![logo](https://vignette.wikia.nocookie.net/onepiece/images/1/1c/Smoker_Anime_Pre_Timeskip_Infobox.png/revision/latest/scale-to-width-down/150)

The **Colonel Smoker** is an expert in smoke. He can even tell if your application is smoking before deploying it to production.

The **Colonel Smoker** find the URLs of your Symfony application (only Symfony is supported now) and looks for `500` errors. 
- helps providing requirements parameters for your dynamic URLs
- uses the routes declared in the Symfony routing by default

## Installation

```bash
composer require --dev lag/colonel-smoker-bundle:dev-master
```

## Configuration
```yaml
# config/packages/dev/lag_smoker.yaml
lag_smoker:
    # Urls providers are here to provide urls to test from Symfony routing.
    providers:
        # The "symfony" url provider is a built-in provider
        symfony:
            strategy: inclusive # Strategies used to build urls (can be inclusive, exclusive, all), explained in the documentation             
            routes:
                - app.homepage # Route names of your application
                - app.my_little_route_name
                - app.my_little_route_name_without_parameters
    
    # Urls parameters mapping defines ways to generate dynamic url parameters
    mapping:
        my_entity: # my_entity can be anything, but it has to be unique in the url parameters mapping
            entity: App\Entity\MyEntity # Your entity containing data to build urls
            pattern: 'app.my_little_route_' # Matches all routes starting with "app.my_little_route_" 
            excludes:
                - app.my_little_route_name_without_parameters # Excludes this route from the mapping
            requirements: # Requirements defined which data will be mapped
                id: id # This will call the $entity->getId() method on your entity and will be mapped with the "id" url parameter
                relationId: relation.id # This will call $entity->getRelation()->getId() on your entity

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


## URLs Providers
You can easily add a requirement provider, by adding a service that implements the `UrlProviderInterface`.

```php

class MyUrlProvider implements UrlProviderInterface
{
    
}

```

## URLs Requirements Providers

The URLs requirements providers are used to provide parameters to routes with dynamic parameters 
(for example `/article/edit/{id}`). 

You can easily add a requirement provider, by adding a service that implements the `RequirementsProviderInterface`.

```php

class MyRequirementsProvider implements RequirementsProviderInterface
{
    
}

```
