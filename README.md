# uvdesk-app-creator
Create uvdesk application.

### Composer
**/composer.json** 
`extra.symfony.endpoint` add `https://api.github.com/repos/shixue/uvdesk-app-creator/contents/index.json?ref=main`
```
{
    "extra": {
        "symfony": {
            "endpoint": [
                "https://api.github.com/repos/uvdesk/recipes/contents/index.json",
                "https://api.github.com/repos/shixue/uvdesk-app-creator/contents/index.json?ref=main",
                "flex://defaults"
            ]
        }
    }
}
```

Or:
`config/bundles.php` add
```
UvdeskAppCreator\CreatorBundle::class => ['dev' => true],
```


**Install**
```
composer require --dev shixue/uvdesk-app-creator:^0.1
composer recipes shixue/uvdesk-app-creator -v
```

### Run
> php bin/console uvdesk:app-creator -h

> php -d memory_limit=-1 bin/console cache:clear && chown -R www:www var/ && chmod -R a+w var/


May need to modify `config/packages/doctrine.yaml`
```
type: attribute
```


**If local services**
`composer.json`
```
"extra": {
  "symfony": {
    "endpoint": [
      "http://127.0.0.1:8000/index.json",
      "flex://defaults"
    ]
  }
}
```

`index.json`
```
"_links": {
    "repository": "local/flex-recipes",
    "origin_template": "{package}:{version}@local/flex-recipes:main",
    "recipe_template": "http://127.0.0.1:8000/recipes/{package_dotted}.{version}.json"
}
```

> php -S 127.0.0.1:8000 -t /flex-recipes

or
> python -m http.server 8000 --directory /flex-recipes

