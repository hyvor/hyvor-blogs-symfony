### Prerequisites
* Symfony 5.4 or higher
* PHP 7.2.5 or higher (the minimum version of PHP supported by Symfony)

### Installing the bundle
Run the following Composer command to install the bundle:
```bash
composer require "hyvor/hyvor-blogs-symfony"
```
### Configuration
Add bundle into your `config/bundles.php` file:

```php
Hyvor\BlogsBundle\HyvorBlogsBundle::class => ['all' => true],
```

Add the following configuration to your `config/packages/hyvor_blogs.yaml` file:
```yaml
hyvor_blogs:
  webhook_path: /path/to/webhook # Path to the webhook, can be anything (ensure it doesn't conflict with other routes)
  blogs:
    -
        subdomain: your-subdomain # Your Hyvor Blog subdomain, e.g. your-blog (see in the Hyvor Blog Console)
        base_path: /your-blog-path # Base path of your blog, e.g. /blog
        delivery_api_key: '**********' # Delivery API key (create one in the Hyvor Blog Console)
        webhook_secret: '**********' # Webhook secret (create one in the Hyvor Blog Console)
        cache_pool: cache.app # Cache pool to use for caching the blog content. By default, it uses the app cache pool
```
