# Colonel Smoker Bundle

The Colonel Smoker is an expert in smoke. He can say if your application is smoking before deploying it in production.

The Colonel Smoker bundle will search into the urls of your Symfony application (only Symfony is supported now), call 
them and check if no 500 errors are encountered. It also provides a easy way to provide requirements parameters for your 
dynamic urls.

By default, the bundle used the routes declared in the Symfony routing.

## Installation

```bash
composer require lag/colonel-smoker-bundle 
```

## Usage

Fist generate the url cache :
```bash
bin/console smoker:generate-cache
```

Then run the smoke tests :
```bash
bin/console smoker:smoke
```


## Urls Providers
You can easily add a requirement provider, by adding a service implements the UrlRequirementsInterface.

```php

class MyRequirementProvider implements UrlRequirementsInterface
{
    
}

```

## Urls Requirements Providers

The urls requirements providers are used to provide parameters to route with dynamic parameters 
(for example `/article/edit/{id}`). 

You can easily add a requirement provider, by adding a service implements the UrlRequirementsInterface.

```php

class MyRequirementProvider implements UrlRequirementsInterface
{
    
}

```
