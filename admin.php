<?php

$this->on('admin.init', function() {

    if (!$this->module('cockpit')->hasaccess('uniqueslugs', 'manage')) {
        return;
    }

    // add settings entry
    $this->on('cockpit.view.settings.item', function () {
        $this->renderView('uniqueslugs:views/partials/settings.php');
    });

    // bind admin routes
    $this->bindClass('UniqueSlugs\\Controller\\Admin', 'uniqueslugs');

});
