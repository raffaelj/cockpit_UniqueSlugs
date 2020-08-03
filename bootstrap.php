<?php
/**
 * unique slugs for Cockpit CMS
 * 
 * @see       https://github.com/raffaelj/cockpit_UniqueSlugs/
 * @see       https://github.com/agentejo/cockpit/
 * 
 * @version   0.5.3
 * @author    Raffael Jesche
 * @license   MIT
 */

$this->module('uniqueslugs')->extend([

    'config' => function() {

        static $config;

        if (!isset($config)) {

            $config = array_replace_recursive(
                $this->app->storage->getKey('cockpit/options', 'unique_slugs', []),
                $this->app->retrieve('unique_slugs', [])
            );

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

            // generate slug on create only or when an existing one is an empty string
            if (!$isUpdate
                || ($isUpdate
                    && array_key_exists($slugName, $entry)
                    && ($entry[$slugName] == '' || $entry[$slugName] == null)))
                {

                $fld = $config['collections'][$name];
                $fld = is_array($fld) ? $fld : [$fld];

                $slugString = $this->findSlugString($entry, $fld, $delim);

                $slug = $this->app->helper('utils')->sluggify($slugString ? $slugString : ($config['placeholder'] ?? 'entry'));

                $slug = $this->incrementSlug($name, $slug, $slugName);

                // save generated slug to "slug"
                $entry[$slugName] = $slug;

            }

            elseif (!empty($config['check_on_update'])
                    && $isUpdate
                    && array_key_exists($slugName, $entry)
                    && ($entry[$slugName] == '' || $entry[$slugName] == null))
                {

                // never trust user input ;-)
                $slug = $this->app->helper('utils')->sluggify($entry[$slugName]);

                if ($this->slugExists($name, $slug, $slugName)
                    && !$this->isOwnSlug($name, $slug, $slugName, $entry['_id'])) {

                    $slug = $this->incrementSlug($name, $slug, $slugName);

                }

                $entry[$slugName] = $slug;

            }

        }

        if (isset($config['localize'][$name])) {

            $locales = array_keys($this->app->retrieve('languages', []));
            $localSlugStrings = [];

            foreach ($locales as $locale) {

                if ($locale == 'default') continue;

                if (!$isUpdate
                    || ($isUpdate
                        && array_key_exists($slugName.'_'.$locale, $entry)
                        && ($entry[$slugName.'_'.$locale] === '' || $entry[$slugName.'_'.$locale] === null)))
                    {

                    $fld = $config['localize'][$name];
                    $fld = is_array($fld) ? $fld : [$fld];

                    $slugString = $this->findSlugString($entry, $fld, $delim, '_'.$locale);

                    $slug = $this->app->helper('utils')->sluggify($slugString ? $slugString : ($config['placeholder'] ?? 'entry'));

                    $slug = $this->incrementSlug($name, $slug, $slugName.'_'.$locale);

                    // save generated slug to "slug_de"
                    $entry[$slugName.'_'.$locale] = $slug;

                }

                elseif (!empty($config['check_on_update'])
                        && $isUpdate
                        && array_key_exists($slugName.'_'.$locale, $entry)
                        && ($entry[$slugName.'_'.$locale] == '' || $entry[$slugName.'_'.$locale] == null))
                    {

                    // never trust user input ;-)
                    $slug = $this->app->helper('utils')->sluggify($entry[$slugName.'_'.$locale]);

                    if ($this->slugExists($name, $slug, $slugName.'_'.$locale)
                        && !$this->isOwnSlug($name, $slug, $slugName.'_'.$locale, $entry['_id'])) {

                        $slug = $this->incrementSlug($name, $slug, $slugName.'_'.$locale);

                    }

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
        $count = $this->slugExists($name, $slug, $slugName);

        // slug doesn't exist yet
        if (!$count) return $slug;

        // try again with a single iteration
        $count = $this->slugExists($name, $slug.'-1', $slugName);

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

    'slugExists' => function($name, $slug, $slugName) {

        // fast single check, should always return 1 or 0
        return $this->app->module('collections')->count($name, [$slugName => $slug]);

    },

    'isOwnSlug' => function($name, $slug, $slugName, $id) {

        $filter = [
            $slugName => $slug,
            '_id' => $id,
        ];
        $projection = [
            '_id' => true,
        ];

        $_id = $this->app->module('collections')->findOne($name, $filter, $projection);

        if (isset($_id['_id']) && $_id['_id'] == $id) {
            return true;
        }

        return false;

    },

]);

// set events
$this->on('cockpit.bootstrap', function() {

    $config = $this->module('uniqueslugs')->config();

    if (!$config) return;

    if (isset($config['collections']) && is_array($config['collections'])) {

        foreach ($config['collections'] as $col => $field) {

            $this->on("collections.save.before.$col", function($name, &$entry, $isUpdate) {
                $entry = $this->module('uniqueslugs')->uniqueSlug($name, $entry, $isUpdate);
            });

        }

    }

}, 100);

// acl
$this('acl')->addResource('uniqueslugs', ['manage']);

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
    include_once(__DIR__.'/admin.php');
}
