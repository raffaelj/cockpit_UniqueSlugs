# UniqueSlugs

Unique slugs for collections in [Cockpit CMS](https://github.com/agentejo/cockpit)

## Installation

Copy this repository into `/addons` and name it `UniqueSlugs` or

```bash
cd path/to/cockpit
git clone https://github.com/raffaelj/cockpit_UniqueSlugs.git addons/UniqueSlugs
```

## How to use

Add these options to `cockpit/config/config.yaml` to specify the collections and field names for slug generation:

```yaml
unique_slugs:
    collections:
        pages     : title
        products  : name
```

all options:

```yaml
# unique slugs
unique_slugs:
    slug_name     : slug        # default: "slug"
    placeholder   : page        # default: "entry"
    delimiter     : |           # default: "|", is used for nested fields
    collections   :
        pages     : title
        products  : name
        something :             # use multiple fields as fallbacks
            - title
            - name
            - image|meta|title  # use nested fields for slugs
    localize      :             # for localized fields, omitted if not set
        pages     : title       # same name lime default language
        products  : name
        something :             # use multiple fields as fallbacks
            - title
            - name
            - image|meta|title  # use nested fields for slugs
```

## Notes:

Don't set `slug_name: fieldname_slug` if you also set `{"slug": true}` in the `fieldname` options for some reason. It should work, but it fails on multilingual setups ([explanation](https://github.com/agentejo/cockpit/issues/906)).

Your collection can have a visible field named "slug", if you want to edit it by hand.

The builtin option to sluggify text fields via options `{"slug": true}` in the 
backend uses Javascript and leads to different results ("ä" becomes "a" 
instead of "ae"). **If you want unique slugs, that option is not necessary anymore.**

The code for this addon is inspired by a [gist from fabianmu](https://gist.github.com/fabianmu/5f73a6c2303e08add4e00dc2e548ef2d).

Thanks to [fabianmu](https://github.com/fabianmu) and [aheinze](https://github.com/aheinze)

## Changelog

**2019-04-23**

* added support for localized fields

**2018-10-25**

* fixed error if nested key doesn't exist
* added placeholder to avoid empty strings as slugs

**2018-10-19**

* added ability to use multiple fields for slug generation
* added nested fields for slug generation
* config name separators were changed from dots to underscores
  * added check for backwards compatibility - will be removed in the future