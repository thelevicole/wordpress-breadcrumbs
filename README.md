# WordPress breadcrumb renderer

## Usage
Start by including the `Breadcrumbs.php` into your themes `functions.php` file.
```php
<?php
	require_once 'path/to/Breadcrumbs.php`;
	...
```
Now you can use the `get_breadcrumbs()` function.

```php
	<div class="breadcrumb-container">
		<?php get_breadcrumbs(); ?>
	</div>
```

## Options
There are a few available options that can be passed as an array.

```php
<?php
$options = [ 'separator' => '&raquo' ];
get_breadcrumbs( $options );
...
```
|Name|Description|Default|
|--|--|--|
|`separator`|Separator for between each breadcrumb.|`/`|
|`id`|Give the generated `<ul>` element an id|`''`|
|`class`|Add css classes to the generated `<ul>` element|`breadcrumbs`|
|`include_front`|If set to true, a front page (e.g. Home) will be prepended to the list|`true`|
|`front_title`|If including a front page, and the front page does not have a post id, give it a title|`Home`|
|`taxonomies`|Include taxonomies in the trail.|Includes all public non "built in" taxonomies using the `get_taxonomies()` function|