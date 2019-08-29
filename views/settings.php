
<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('UniqueSlugs')</span></li>
    </ul>
</div>

<div class="uk-width-1-1 uk-width-xlarge-3-4 uk-container-center" riot-view>

    <form class="uk-form" onsubmit="{ submit }">

        <div class="">

            <div class="uk-panel uk-panel-box uk-panel-card uk-margin uk-width-1-1">
                
                <div class="uk-width-1-1 uk-grid uk-container-center">
            
                    <div class="uk-width-small-1-2 uk-width-medium-1-4 uk-margin-small">
                        <label class="uk-display-block uk-margin-small">
                            @lang('Slug name')
                            <i class="uk-icon-info-circle" title="@lang('Default: slug')" data-uk-tooltip></i>
                        </label>
                        <field-text bind="config.slug_name"></field-text>
                    </div>

                    <div class="uk-width-small-1-2 uk-width-medium-1-4 uk-margin-small">
                        <label class="uk-display-block uk-margin-small">
                            @lang('Placeholder')
                            <i class="uk-icon-info-circle" title="@lang('Default: entry - fallback, if title is empty')" data-uk-tooltip></i>
                        </label>
                        <field-text bind="config.placeholder"></field-text>
                    </div>

                    <div class="uk-width-small-1-2 uk-width-medium-1-4 uk-margin-small">
                        <label class="uk-display-block uk-margin-small">
                            @lang('Check on update')
                            <i class="uk-icon-info-circle" title="@lang('Default: false - Enabled: Check for uniqueness on each entry update. Disabled: Check only, when the entry is created.')" data-uk-tooltip></i>
                        </label>
                        <field-boolean bind="config.check_on_update"></field-boolean>
                    </div>

                    <div class="uk-width-small-1-2 uk-width-medium-1-4 uk-margin-small">
                        <label class="uk-display-block uk-margin-small">
                            @lang('Delimiter')
                            <i class="uk-icon-info-circle" title="@lang('Default: | - If you use nested fields for slug generation, like image titles, use the delimiter. Example: image|meta|title')" data-uk-tooltip></i>
                        </label>
                        <field-text bind="config.delimiter"></field-text>
                    </div>

                </div>
            </div>
            
            <div class="uk-panel uk-panel-box uk-panel-card uk-margin uk-width-1-1">

                <label class="uk-display-block uk-margin-small">@lang('Collections')</label>

                <div class="uk-width-1-1">

                    <div class="uk-width-1-1">

                        <div class="uk-width-1-1 uk-panel-box uk-panel-card uk-margin-small" each="{ collection in collections }">

                            <label class="uk-display-block uk-margin-small">{ collection.label || collection.name }</label>

                            <div class="">

                                <div class="uk-display-inline-block">
                                <field-tags bind="config.collections.{collection.name}" autocomplete="{ autocomplete(collection.fields) }" placeholder="@lang('Add field name')"></field-tags>
                                </div>

                                <div class="uk-display-inline-block">
                                <i class="uk-icon-globe" title="@lang('Localize')" data-uk-tooltip></i>

                                <field-tags class="uk-display-inline-block" bind="config.localize.{collection.name}" autocomplete="{ autocomplete(collection.fields) }" placeholder="@lang('Add field name')"></field-tags>
                                </div>

                            </div>
                        </div>

                    </div>

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
            
            console.log(this.config);

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
                   console.log(data);
                } else {
                    App.ui.notify("Saving failed.", "danger");
                }

            });

        }

    </script>

</div>
