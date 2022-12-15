
<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>UniqueSlugs</span></li>
    </ul>
</div>

<div class="uk-width-1-1 uk-width-xlarge-3-4 uk-container-center" riot-view>

    <form class="uk-form" onsubmit="{ submit }">

        <div class="uk-panel uk-panel-box uk-panel-box-primary uk-panel-card uk-panel-header uk-margin">

            <h2 class="uk-panel-title">@lang('Config')</h2>

            <div class="uk-grid uk-grid-match uk-grid-width-medium-1-2" data-uk-grid-margin>

                <div>
                    <div class="uk-panel-box uk-panel-card">
                        <label class="uk-display-block uk-margin-small">
                            @lang('Slug name')
                        </label>
                        <input type="text" class="uk-width-1-1" bind="config.slug_name" />
                        <div class="uk-alert">@lang('Default:') <code>slug</code></div>
                    </div>
                </div>

                <div>
                    <div class="uk-panel-box uk-panel-card">
                        <label class="uk-display-block uk-margin-small">
                            @lang('Placeholder')
                        </label>
                        <input type="text" class="uk-width-1-1" bind="config.placeholder" />
                        <div class="uk-alert">@lang('Default:') <code>entry</code><br>(@lang('Fallback, if title is empty'))</div>
                    </div>
                </div>

                <div>
                    <div class="uk-panel-box uk-panel-card">
                        <label class="uk-display-block uk-margin-small">
                            @lang('Check on update')
                        </label>
                        <field-boolean bind="config.check_on_update"></field-boolean>
                        <div class="uk-alert">@lang('Default:') <code>false</code><br>@lang('Enabled: Check for uniqueness on each entry update. Disabled: Check only, when the entry is created.')</div>
                    </div>
                </div>

                <div>
                    <div class="uk-panel-box uk-panel-card">
                        <label class="uk-display-block uk-margin-small">
                            @lang('Delimiter')
                        </label>
                        <input type="text" class="uk-width-1-1" bind="config.delimiter" />
                        <div class="uk-alert">@lang('Default:') <code>|</code><br>@lang('If you use nested fields for slug generation, like image titles, use the delimiter.')<br>@lang('Example:') <code>image|meta|title</code></div>
                    </div>
                </div>

            </div>
        </div>

        <div class="uk-panel uk-panel-box uk-panel-box-primary uk-panel-card uk-panel-header uk-margin">

            <h2 class="uk-panel-title">@lang('Collections')</h2>

            <div class="uk-panel-box uk-panel-card uk-panel-header uk-margin" each="{ collection in collections }">

                <h3 class="uk-panel-title">{ collection.label || collection.name }</h3>

                <div class="uk-display-inline-block">
                    <field-tags bind="config.collections.{collection.name}" autocomplete="{ autocomplete(collection.fields) }" placeholder="@lang('Add field name')"></field-tags>
                </div>

                <div class="uk-display-inline-block">
                    <i class="uk-icon-globe" title="@lang('Localize')" data-uk-tooltip></i>

                    <field-tags class="uk-display-inline-block" bind="config.localize.{collection.name}" autocomplete="{ autocomplete(collection.fields) }" placeholder="@lang('Add field name')"></field-tags>
                </div>

            </div>

        </div>

        <cp-actionbar>
            <div class="uk-container uk-container-center">
                <button class="uk-button uk-button-large uk-button-primary">@lang('Save')</button>
                <a class="uk-button uk-button-link" href="@route('/settings')">
                    <span>@lang('Cancel')</span>
                </a>
            </div>
        </cp-actionbar>

    </form>

    <script type="view/script">

        var $this = this;

        riot.util.bind(this);

        this.config = {{ !empty($config) ? json_encode($config) : '{}' }};
        this.collections = {{ json_encode($collections) }};

        this.on('mount', function() {

            // bind global command + save
            Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {
                e.preventDefault();
                $this.submit();
                return false;
            });

        });

        autocomplete(fields) {

            if (!fields || !Array.isArray(fields)) return;

            return fields.map(x => x.name);

        }

        submit(e) {

            if (e) e.preventDefault();

            App.request('/uniqueslugs/saveConfig', {config:this.config}).then(function(data){

               if (data) {
                   App.ui.notify("Saving successful", "success");
                } else {
                    App.ui.notify("Saving failed.", "danger");
                }

            });

        }

    </script>

</div>
