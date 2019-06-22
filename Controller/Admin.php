<?php

namespace UniqueSlugs\Controller;

class Admin extends \Cockpit\AuthController {

    public function index() {}

    public function settings() {

        $config = $this->app->module('uniqueslugs')->config();

        // force array notation if values are strings
        if (isset($config['collections'])) {
            foreach($config['collections'] as &$val) {
                if (is_string($val)) $val = [$val];
            }
        }

        if (isset($config['localize'])) {
            foreach($config['localize'] as &$val) {
                if (is_string($val)) $val = [$val];
            }
        }

        $collections = $this->app->module('collections')->getCollectionsInGroup();

        return $this->render('uniqueslugs:views/settings.php', compact('config', 'collections'));

    }

    public function saveConfig() {

        $config = $this->app->param('config');

        if ($config) {

            $this->app->storage->setKey('cockpit/options', 'unique_slugs', $config);

            return $config;

        }

        return false;

    }

}
