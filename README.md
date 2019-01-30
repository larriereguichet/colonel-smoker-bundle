# Colonel Smoker Bundle

![logo](https://vignette.wikia.nocookie.net/onepiece/images/1/1c/Smoker_Anime_Pre_Timeskip_Infobox.png/revision/latest?cb=20160102184944 | width=50)

The **Colonel Smoker** is an expert in smoke. He can even tell if your application is smoking before deploying it to production.

The **Colonel Smoker** find the URLs of your Symfony application (only Symfony is supported now) and looks for `500` errors. 
- helps providing requirements parameters for your dynamic URLs
- uses the routes declared in the Symfony routing by default

## Installation

```bash
composer require --dev lag/colonel-smoker-bundle:dev-master
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
