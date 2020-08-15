# Changelog

## 0.5.4

* fixed missing `__DIR__` in admin include
* added `composer.json`

## 0.5.3

* moved collections events into `cockpit.bootstrap` event with priority 100 to allow dynamic config modifications before the events are assigned

## 0.5.2

* fixed overwriting of slug when saving only partial data (via custom php script or via api)

## 0.5.1

* fixed: GUI didn't save on first run, because of a wrong variable type (empty object/array)

## 0.5.0

* added GUI
* removed fallback for old configuration name separators (dots vs. underscores) - deprecated notice was there since 2018-10-19

## 0.4.3

* fixed wrong localization if "default" language was renamed

## 0.4.2

* rewrite to object oriented style
* added support for localized fields
* incremental count didn't work correctly in the past - fixed
* added optional unique check on each update, e. g. if user changes slug by hand, enable it with `check_on_update: true`

## ...

* I didn't really track changes, other than in the commits...

## 2018-10-25

* fixed error if nested key doesn't exist
* added placeholder to avoid empty strings as slugs

## 2018-10-19

* added ability to use multiple fields for slug generation
* added nested fields for slug generation
* config name separators were changed from dots to underscores
  * added check for backwards compatibility - will be removed in the future
