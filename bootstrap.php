<?php
/**
 * unique slugs for Cockpit CMS
 * 
 * @see       https://github.com/raffaelj/cockpit_UniqueSlugs/
 * @see       https://github.com/agentejo/cockpit/
 * 
 * @version   0.3.0
 * @author    Raffael Jesche
 * @license   MIT
 */

$config = $app['unique_slugs'] ?? null;

// config name separators were changed from dots to underscores
// this check is for backwards compatibility and
// will be removed in a future version
if (!$config && isset($app['unique.slugs'])) {
    $config = $app['unique.slugs'] ?? null;
    if (isset($config['all.collections']))
        $config['all_collections'] = $app['unique_slugs']['all.collections'];
    if (isset($config['slug.name']))
        $config['slug_name'] = $app['unique_slugs']['slug.name'];
}

// postfix is for localization, e. g. "_de"
function findSlugString($entry, $fld, $delim, $postfix = '') {

    $slugString = null;
    foreach ($fld as $val) {

        if (is_string($val) && strpos($val, $delim) === false) {
            $slugString = !empty($entry[$val.$postfix]) ? $entry[$val.$postfix] : null;
            if ($slugString) break;
            continue;
        }

        // loop to get nested fields for slug
        $current = $entry;
        $i = 0;
        foreach (explode($delim, $val) as $key) {
            if (!isset($current[$i == 0 ? $key.$postfix : $key])){
                $current = null;
                break;
            }
            $current = &$current[$i == 0 ? $key.$postfix : $key];
            $i++;
        }

        $slugString = is_string($current) && !empty($current) ? $current : $slugString;
        if ($slugString) break;
    }

    return $slugString;

}


$uniqueSlug = function($name, &$entry, $isUpdate) use ($app, $config) {

    if (!$config) return;

    // get slug name from config.yaml
    $slugName = isset($config['slug_name']) ? $config['slug_name'] : 'slug';

    // create empty slug field if it doesn't exist
    if (!isset($entry[$slugName]))
        $entry[$slugName] = '';

    // get field name from config.yaml
    if (isset($config['collections'][$name])) {

        $fld = $config['collections'][$name];
        $fld = is_array($fld) ? $fld : [$fld];

        $delim = $config['delimiter'] ?? '|';

        $slugString = findSlugString($entry, $fld, $delim);

    }

    if (isset($config['localize'][$name])) {

        $locales = array_keys($app->retrieve('languages', []));
        $localSlugString = [];
        $delim = $config['delimiter'] ?? '|';

        foreach ($locales as $locale) {

            $fld = $config['localize'][$name];
            $fld = is_array($fld) ? $fld : [$fld];

            $localSlugStrings[$locale] = findSlugString($entry, $fld, $delim, '_'.$locale);

        }

    }

    else {

        // to do, use defaults like title/name/first entry/...

        // setting `all_collections : true` has no effect right now

        return;

    }

    // generate slug on create only or when an existing one is empty
    if (!$isUpdate || ($isUpdate && trim($entry[$slugName]) == '')) {

        $slug = $app->helper('utils')->sluggify($slugString ? $slugString : ($config['placeholder'] ?? 'entry'));

        // count entries with the same slug
        $count = $app->module('collections')->count($name, [$slugName => $slug]);

        // if slug exists already, postfix with incremental count
        if ($count > 0)
            $slug = "{$slug}-{$count}";

        // save generated slug to field with name $slugName
        $entry[$slugName] = $slug;

    }

    if (isset($localSlugStrings)) {

        foreach ($localSlugStrings as $locale => $slugString) {

            $slug = $app->helper('utils')->sluggify($slugString ? $slugString : ($config['placeholder'] ?? 'entry'));

            // count entries with the same slug
            $count = $app->module('collections')->count($name, [$slugName.'_'.$locale => $slug]);

            // if slug exists already, postfix with incremental count
            if ($count > 0)
                $slug = "{$slug}-{$count}";

            // save generated slug to field with name $slugName
            $entry[$slugName.'_'.$locale] = $slug;

        }

    }

};

// set event handler with uniqueSlug function
if ($config) {
    if (isset($config['all_collections']) && $config['all_collections'])
        $app->on("collections.save.before", $uniqueSlug);
    
    elseif (isset($config['collections']))
        foreach ($config['collections'] as $col => $field)
            $app->on("collections.save.before.$col", $uniqueSlug);
}
