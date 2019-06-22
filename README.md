# UniqueSlugs

Unique slugs for collections in [Cockpit CMS][2]

## Installation

Copy this repository into `/addons` and name it `UniqueSlugs` or

```bash
cd path/to/cockpit
git clone https://github.com/raffaelj/cockpit_UniqueSlugs.git addons/UniqueSlugs
```

## How to use

Use the GUI. If you are no admin, your user group needs manage rights.

![uniqueslugs-gui](https://user-images.githubusercontent.com/13042193/59967705-2c8d4200-952e-11e9-95d0-82e1cc21e4ad.png)

Or add these options to `cockpit/config/config.yaml` to specify the collections and field names for slug generation:

```yaml
# UniqueSlugs example
unique_slugs:
    collections:
        pages     : title
        products  : name

# ACL example
groups:
    manager:
        cockpit:
            backend: true
        uniqueslugs:
            manage: true
```

all options:

```yaml
# unique slugs
unique_slugs:
    slug_name      : slug        # default: "slug"
    placeholder    : page        # default: "entry"
    check_on_update: true        # default: false, unique checks on each update (if user changes slug by hand)
    delimiter      : |           # default: "|", is used for nested fields
    collections    :
        pages      : title
        products   : name
        something  :             # use multiple fields as fallbacks
            - title
            - name
            - image|meta|title   # use nested fields for slugs
    localize       :             # for localized fields, omitted if not set
        pages      : title       # field name without suffix ("_de")
        products   : name
        something  :             # use multiple fields as fallbacks
            - title
            - name
            - image|meta|title   # use nested fields for slugs
```

## Notes:

Hardcoded settings in the config file will override gui settings.

Don't set `slug_name: fieldname_slug` if you also set `{"slug": true}` in the `fieldname` options for some reason. It should work, but it fails on multilingual setups ([explanation][3]).

Your collection can have a visible field named "slug", if you want to edit it by hand.

The builtin option to sluggify text fields via options `{"slug": true}` in the 
backend uses Javascript. **If you want unique slugs, that option is not necessary anymore.**

The code for this addon is inspired by a [gist from fabianmu][4].

Thanks to [fabianmu][5] and [aheinze][6]

[1]: https://github.com/agentejo/cockpit/commit/fc7bb9cbe7dc2bb69f8f34ca2e899b9ad49f33fc#diff-dbdace793615e1dc2b38f69bdac96950
[2]: https://github.com/agentejo/cockpit
[3]: https://github.com/agentejo/cockpit/issues/906
[4]: https://gist.github.com/fabianmu/5f73a6c2303e08add4e00dc2e548ef2d
[5]: https://github.com/fabianmu
[6]: https://github.com/aheinze
