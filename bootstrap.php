<?php

/*
 * This is a dummy bootstrap file to prevent a
 * `Fatal error: require(): Failed opening required ...`
 * when `\Lime\App` tries to load the module without an
 * existing `bootstrap.php`.
 *
 * Don't copy the whole repository into your addons folder.
 * You only need the one, that is named like this addon.
 *
 * Sorry for the confusion, but I like to keep some data
 * (e. g. .gitignore or a docs folder) in the root, that
 * aren't necessary in production use of the addon.
 *
 */

$app->on('app.layout.contentbefore', function(){
    echo '<p class="uk-panel"><span class="uk-badge uk-badge-warning"><i class="uk-margin-small-right uk-icon-warning"></i>' . basename(__DIR__) . '</span> You copied the wrong addon folder. Have a look at the addon\'s readme file for instructions.</p>';
});
