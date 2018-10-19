# UniqueSlugs

Unique slugs for collections in Cockpit - https://github.com/agentejo/cockpit

## How to use

Copy the folder `UniqueSlugs` into `cockpit/addons/UniqueSlugs`.

Add these options to `cockpit/config/config.yaml` to specify the collections and field names for slug generation:

```yaml
unique.slugs:
    collections:
        pages     : title
        products  : name
```

all options:

```yaml
# unique slugs
unique.slugs:
    all.collections : false # default: false
    slug.name       : slug  # default: "slug"
    delimiter       : |     # default: "|", is used for nested fields
    collections:
        pages     : title
        products  : name
        something :         # use multiple fields as fallbacks
            - title
            - name
            - image|meta|title  # use nested fields for slugs
```

## Notes:

Your collection doesn't need a visible field named "slug", but you can't edit
it if you don't have one.

If you want to hide the slug field for non-admins, just add the following code
to the read permissions of your collection:

```php
<?php
if ($context->user && $context->user['group'] != 'admin')
  $context->options['fields']['slug'] = false;
```

The builtin option to sluggify fields via options `{"slug": true}` in the 
backend uses Javascript and leads to different results ("ä" becomes "a" 
instead of "ae"). If you want unique slugs, that option is not necessary anymore..

This code is a modified version of https://gist.github.com/fabianmu/5f73a6c2303e08add4e00dc2e548ef2d
Thanks to https://github.com/fabianmu and https://github.com/aheinze
