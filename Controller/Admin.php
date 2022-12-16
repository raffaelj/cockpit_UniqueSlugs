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

    /**
     * Create slugs in existing entries, that were created before
     * installing/configuring the UniqueSlugs addon.
     *
     * TODO: check for missing localized slugs, where the slug with default language exists
     *
     * @return array _id and main slug of updated entries, grouped by collection
     */
    public function updateEntriesWithoutSlug() {

        $config = $this->app->module('uniqueslugs')->config();

        $slugName = isset($config['slug_name']) ? $config['slug_name'] : 'slug';

        $results = [];

        foreach ($config['collections'] as $name => $fieldNames) {

            $entries = (array)$this->app->storage->find("collections/{$name}");

            foreach ($entries as &$entry) {

                if (!array_key_exists($slugName, $entry)
                    || $entry[$slugName] === null
                    || $entry[$slugName] === ''
                ) {

                    $isUpdate = false;
                    $entry = $this->app->module('uniqueslugs')->getEntryWithUniqueSlug($name, $entry, $isUpdate);

                    $ret = $this->app->storage->save("collections/{$name}", $entry);

                    if ($ret) {
                        $results[$name][] = [
                            '_id' => $entry['_id'],
                            $slugName => $entry[$slugName],
                        ];
                    }
                }

            }

        }

        return $results;

    }

}
