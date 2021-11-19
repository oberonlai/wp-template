# PageTemplates
A simple class that allows registering WordPress any templates from plugins. Include page templates, custom post type templates and WooCommerce templates

## Installation
```
composer require oberonlai/wp-template-loader
```

## Usage

First, initialize the class with the plugin's directory path and template folder. For example, if you place the template files in a folder which name is 'templates', you can assing the path.

```php
use ODS\TemplateLoader;
$template = new TemplateLoader( plugin_dir_path( __FILE__ ) . 'templates/' );
```

Don't forget the last slash in folder path.

### Add page template

Page template is the template can be used repeatedly in page editor. You can use ```add_page()``` to assign the page template. There are two arguments in ```add_page()```:

- $file - The page template file name
- $name - Human-readable template name

```php
use ODS\TemplateLoader;
$template = new TemplateLoader( plugin_dir_path( __FILE__ ) . 'templates/' );
$template->add_page( 'template-demo.php', 'my-template' );
```

### Add page slug template.

Page slug template is the template for same slug name. You can use the ```page-demo.php``` template if your page slug's name is 'demo'. There are two arguments:

- $file The page template file name
- $slug For matched page slug

```php
use ODS\TemplateLoader;
$template = new TemplateLoader( plugin_dir_path( __FILE__ ) . 'templates/' );
$template->add_page_slug( 'page-test.php', 'test' );
```

### Add custom post type template

Custom post type template includes single and archive page. You use the method ```add_post()``` to assign template file. There are three arguments :

- $file The page template file name
- $type Custom post type name
- $position Template position optional single, archive

```php
use ODS\TemplateLoader;
$template = new TemplateLoader( plugin_dir_path( __FILE__ ) . 'templates/' );
$template->add_post( 'single-book.php', 'book', 'single' );
$template->add_post( 'single-book.php', 'book', 'archive' );
```

### Override WordPress template

WordPress template includes serveral filters to override.

https://developer.wordpress.org/reference/hooks/type_template/

You use the method ```add_wp()``` to assign template file. There are three arguments:

- $file The page template file name
- $position Template position

```php
use ODS\TemplateLoader;
$template = new TemplateLoader( plugin_dir_path( __FILE__ ) . 'templates/' );
$template->add_wp( 'author.php', 'author' );
$template->add_wp( '404.php', '404' );
```

## Credits
The ```add_page()``` method was adapted from http://www.wpexplorer.com/wordpress-page-templates-plugin/