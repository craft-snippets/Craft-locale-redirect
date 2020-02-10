# Locale redirect plugin for Craft CMS 3.x

PLUGIN IS STILL IN BETA AND SHOULD NOT BE USED IN PRODUCTION.

## Installation

```
composer require craftsnippets/locale-redirect
```

## Usage
Put this into `config/locale-redirect.php`:
```
<?php
return [
	'redirectMapping' => [
	    'en' => 'site_en_handle',
	    'ru' => 'site_ru_handle',
	],
];
```
First site will be used as default one if no match can be made.

---------

Brought to you by [Piotr Pogorzelski](http://craftsnippets.com)
