<head>

    <base href="{$this->basePath()}">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {$this->headMeta()}

    {$this->headTitle()}

    {*
        Use prependFile instead of appendFile
        so that these scripts are loaded
        prior to any script from action partials!
    *}

    {if $this->useMinified and $this->minifiedSuffix}
        {if $this->environment eq 'development'}
            {$this->headLink(['rel' => 'shortcut icon', 'href' => 'images/favicon.ico'])
                ->appendStylesheet('css/production_backend.css')
                ->appendStylesheet('css/production_'|cat:$this->minifiedSuffix|cat:'.css')
            }

            {$script = $this->headScript()
                ->appendFile('js/production_backend_libs.js')
                ->appendFile('js/production_backend.js')
                ->appendFile('js/production_'|cat:$this->minifiedSuffix|cat:'.js')
            }
        {else}
            {$this->headLink(['rel' => 'shortcut icon', 'href' => 'images/favicon.ico'])
                ->appendStylesheet('css/production_backend.min.css')
                ->appendStylesheet('css/production_'|cat:$this->minifiedSuffix|cat:'.min.css')
            }

            {$script = $this->headScript()
                ->prependFile('https://secure.pay1.de/client-api/js/ajax.js', 'text/javascript')
                ->appendFile('js/production_backend_libs.js')
                ->appendFile('js/production_backend.min.js')
                ->appendFile('js/production_'|cat:$this->minifiedSuffix|cat:'.min.js')
            }
        {/if}

        {$this->headScript()->appendFile('common/tinymce/tinymce.min.js')}
    {else}

        {$this
            ->headLink(['rel' => 'shortcut icon', 'href' => 'images/backend/icon_favicon.ico'])

            ->prependStylesheet('backend/css/media.css')
            ->prependStylesheet('backend/css/style.css')
            ->prependStylesheet('backend/css/header.css')

            ->prependStylesheet('backend/css/forms.css')

            ->prependStylesheet('backend/css/lightboxes.css')
            ->prependStylesheet('backend/css/icons.css')
            ->prependStylesheet('backend/css/buttons.css')

            ->prependStylesheet('backend/css/colorpicker.css')
            ->prependStylesheet('backend/css/dropdown.css')

            ->prependStylesheet('backend/css/tabledefault.css')
            ->prependStylesheet('backend/css/formdefault.css')

            ->prependStylesheet('common/css/ffbCalendar.css')
            ->prependStylesheet('common/css/ffbAccordion.css')
            ->prependStylesheet('common/css/reset.css')
        }

        {$this
            ->headScript()
            ->prependFile('https://secure.pay1.de/client-api/js/ajax.js', 'text/javascript')
            ->prependFile('backend/js/form/FormObject.js')
            ->prependFile('backend/js/form/FormInitializer.js')

            ->prependFile('backend/js/element/ffbHorizontalScroll.js')
            ->prependFile('backend/js/element/ffbNavigationMenu.js')
            ->prependFile('backend/js/element/ffbVerticalScroll.js')
            ->prependFile('backend/js/element/PaneManager.js')
            ->prependFile('backend/js/App.js')
            ->prependFile('js/backend/element/LinkedListLanguageSwitcher.js')
            ->prependFile('js/backend/element/SimpleGallery.js')

            ->prependFile('common/js/elements/FilterTableHelper.js')
            ->prependFile('common/js/elements/TableHelper.js')

            ->prependFile('common/js/controls/DesignSelect.js')
            ->prependFile('common/js/controls/MultipleSelect.js')
            ->prependFile('common/js/controls/FilesUpload.js')
            ->prependFile('common/js/controls/FileUpload.js')
            ->prependFile('common/js/controls/EditorFileUpload.js')
            ->prependFile('common/js/controls/TeaserSelect.js')

            ->prependFile('common/js/libraries/colorpicker.js')
            ->prependFile('common/js/libraries/ffbTabs.js')
            ->prependFile('common/js/libraries/ffbFileUpload.js')
            ->prependFile('common/js/libraries/ffbDropdown.js')
            ->prependFile('common/js/libraries/ffbDate.js')
            ->prependFile('common/js/libraries/ffbCalendar.js')
            ->prependFile('common/js/libraries/ffbScroll.js')
            ->prependFile('common/js/libraries/ffbSwitch.js')
            ->prependFile('common/js/libraries/ffbAccordion.js')
            ->prependFile('common/js/libraries/ffbForm.js')
            ->prependFile('common/js/libraries/ffbLightbox.js')
            ->prependFile('common/js/libraries/ffbAjax.js')
            ->prependFile('common/js/libraries/ffbTranslator.js')
            ->prependFile('common/js/libraries/ffbTooltip.js')

            ->prependFile('common/tinymce/tinymce.min.js')

            ->prependFile('common/js/libraries/jquery-ui-1.10.4.custom.js')
            ->prependFile('common/js/libraries/jquery.min.js')
            ->prependFile('common/js/libraries/modernizr.custom.65934.js')
        }
    {/if}

</head>