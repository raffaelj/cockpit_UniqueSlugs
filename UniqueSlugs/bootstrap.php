<?php

$uniqueSlug = function($name, &$entry, $isUpdate) use ($app) {

    // get slug name from config.yaml
    $slugName = isset($app['unique.slugs']['slug.name']) ? $app['unique.slugs']['slug.name'] : 'slug';

    // create empty slug field if it doesn't exist
    if (!isset($entry[$slugName]))
        $entry[$slugName] = '';

    // get field name from config.yaml
    if (isset($app['unique.slugs']['collections'][$name])) {

        $fld = $app['unique.slugs']['collections'][$name];
        $fld = is_array($fld) ? $fld : [$fld];

        $delim = $app['unique.slugs']['delimiter'] ?? '|';

        $slugString = null;
        foreach ($fld as $val) {

            if (is_string($val) && strpos($val, $delim) === false) {
                $slugString = $entry[$val] ?? null;
                if ($slugString) break;
            }

            $current = $entry;
            foreach (explode($delim, $val) as $key) {
                $current = &$current[$key];
            }

            $slugString = is_string($current) ? $current : $slugString;
            if ($slugString) break;
        }
    }
    else {
        
        // to do, use defaults like title/name/first entry/...
        return;

    }

    // generate slug on create only or when an existing one is empty
    if (!$isUpdate || ($isUpdate && trim($entry[$slugName]) == '')) {

        // $slug = $app->helper('utils')->sluggify($entry[$fld] ?? '');
        $slug = $app->helper('utils')->sluggify($slugString);

        // count entries with the same slug
        $entries = $app->module('collections')->count($name, [$slugName => $slug]);
        
        // if slug is existing already, postfix with incremental count
        if ($entries > 0)
            $slug = "{$slug}-{$entries}";
        
        // save generated slug to field with name $slugName
        $entry[$slugName] = $slug;
    }

};

// set event handler with uniqueSlug function
if (isset($app['unique.slugs'])){
    
    if (isset($app['unique.slugs']['all.collections']) && $app['unique.slugs']['all.collections'])
        $app->on("collections.save.before", $uniqueSlug);
    
    elseif (isset($app['unique.slugs']['collections']))
        foreach ($app['unique.slugs']['collections'] as $col => $field)
            $app->on("collections.save.before.$col", $uniqueSlug);
}
