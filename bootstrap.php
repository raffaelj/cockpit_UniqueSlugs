<?php

$uniqueSlug = function($name, &$entry, $isUpdate) use ($app) {
    
    // get slug name from config.yaml
    $slugName = isset($app['unique.slugs']['slug.name']) ? $app['unique.slugs']['slug.name'] : 'slug';
    
    // get field name from config.yaml
    if (isset($app['unique.slugs']['collections'][$name])) {
        $field = $app['unique.slugs']['collections'][$name];
    }
    else {
        
        // to do, use defaults like title/name/first entry/...
        return;
        
    }
    
    // create empty slug field if it doesn't exist
    if (!isset($entry[$slugName]))
        $entry[$slugName] = "";
    
    // generate slug on create only or when an existing one is empty
    if (!$isUpdate || ($isUpdate && trim($entry[$slugName]) == '')) {
      
        // generate slug based on field name
        $slug = $app->helper('utils')->sluggify($entry[$field]);
        
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
