<?php
/**
 * unique slugs for Cockpit CMS
 * 
 * @see       https://github.com/raffaelj/cockpit_UniqueSlugs/
 * @see       https://github.com/agentejo/cockpit/
 * 
 * @version   0.4.1
 * @author    Raffael Jesche
 * @license   MIT
 */

$this->module('uniqueslugs')->extend([

    'config' => function() {

        static $config;

        if (!isset($config)) {

            $config = $this->app->retrieve('unique_slugs', null);

            // config name separators were changed from dots to underscores
            // this check is for backwards compatibility and
            // will be removed in a future version
            if (!$config && $config = $this->app->retrieve('unique.slugs', null)) {

                if (isset($config['all.collections']))
                    $config['all_collections'] = $config['all.collections'];

                if (isset($config['slug.name']))
                    $config['slug_name'] = $config['slug.name'];

            }

        }

        return $config;

    },

    'uniqueSlug' => function($name, $entry, $isUpdate) {

        $config = $this->config();

        if (!$config) return $entry;

        // get slug name
        $slugName = isset($config['slug_name']) ? $config['slug_name'] : 'slug';

        // delimiter for nested fields, e. g. "tags|0" or "asset|title"
        $delim = $config['delimiter'] ?? '|';

        // get field name
        if (isset($config['collections'][$name])) {

            $fld = $config['collections'][$name];
            $fld = is_array($fld) ? $fld : [$fld];

            $slugString = $this->findSlugString($entry, $fld, $delim);

            // generate slug on create only or when an existing one is empty
            if (!$isUpdate || ($isUpdate && empty($entry[$slugName]))) {

                $slug = $this->app->helper('utils')->sluggify($slugString ? $slugString : ($config['placeholder'] ?? 'entry'));

                $slug = $this->incrementSlug($name, $slug, $slugName);

                // save generated slug to "slug"
                $entry[$slugName] = $slug;

            }

        }

        if (isset($config['localize'][$name])) {

            $locales = array_keys($this->app->retrieve('languages', []));
            $localSlugStrings = [];

            foreach ($locales as $locale) {

                $fld = $config['localize'][$name];
                $fld = is_array($fld) ? $fld : [$fld];

                $slugString = $this->findSlugString($entry, $fld, $delim, '_'.$locale);

                if (!$isUpdate || ($isUpdate && empty($entry[$slugName.'_'.$locale]))) {

                    $slug = $this->app->helper('utils')->sluggify($slugString ? $slugString : ($config['placeholder'] ?? 'entry'));

                    $slug = $this->incrementSlug($name, $slug, $slugName.'_'.$locale);

                    // save generated slug to "slug_de"
                    $entry[$slugName.'_'.$locale] = $slug;

                }

            }

        }

        return $entry;

    },

    'findSlugString' => function($entry, $fld, $delim, $suffix = '') {

        $slugString = null;
        foreach ($fld as $val) {

            if (strpos($val, $delim) === false) {
                $slugString = !empty($entry[$val.$suffix]) ? $entry[$val.$suffix] : null;
                if ($slugString) break;
                continue;
            }

            // loop to get nested fields for slug
            $current = $entry;
            $i = 0;
            foreach (explode($delim, $val) as $key) {
                if (!isset($current[$i == 0 ? $key.$suffix : $key])){
                    $current = null;
                    break;
                }
                $current = &$current[$i == 0 ? $key.$suffix : $key];
                $i++;
            }

            $slugString = is_string($current) && !empty($current) ? $current : $slugString;
            if ($slugString) break;
        }

        return $slugString;

    },

    'incrementSlug' => function($name, $slug, $slugName) {

        // fast single check, should always return 1 or 0
        $count = $this->app->module('collections')->count($name, [$slugName => $slug]);

        // slug doesn't exist yet
        if (!$count) return $slug;

        // try again with a single iteration
        $count = $this->app->module('collections')->count($name, [$slugName => $slug.'-1']);

        if (!$count) return $slug.'-1';

        // more than 1 duplicate - use a regex
        // a simple count doesn't work, because entries could be deleted
        $options = [
            'filter' => [ // find "title" and "title-1", but not "title-test-1" or "title-2-1"
                $slugName => ['$regex' => '/^'.$slug.'(-\d+|$)$/'],
            ],
            'fields' =>  [
                $slugName => true,
                '_id' => false,
            ],
        ];

        $slugs = array_column($this->app->module('collections')->find($name, $options), $slugName);

        natsort($slugs); // sort natural

        $highest = end($slugs);
        $count = substr($highest, strrpos($highest, '-') + 1);

        return $slug . '-' . ($count + 1);

    },

]);

// set events
if ($config = $this->module('uniqueslugs')->config()) {

    if (isset($config['collections']) && is_array($config['collections'])) {

        foreach ($config['collections'] as $col => $field) {

            $app->on("collections.save.before.$col", function($name, &$entry, $isUpdate) {
                $entry = $this->module('uniqueslugs')->uniqueSlug($name, $entry, $isUpdate);
            });

        }

    }

}
