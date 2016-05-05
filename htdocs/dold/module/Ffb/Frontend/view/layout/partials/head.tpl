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

    {$this
        ->headLink(['rel' => 'shortcut icon', 'href' => 'images/frontend/icon_favicon.ico'])

        ->prependStylesheet('css/frontend/style.css')
        ->prependStylesheet('common/bootstrap/css/bootstrap.css')
        ->prependStylesheet('common/bootstrap/css/bootstrap-theme.css')
    }

    {$this
        ->headScript()

        ->prependFile('common/js/libraries/ffbTranslator.js')

        ->prependFile('common/bootstrap/js/bootstrap.js')

        ->prependFile('common/js/libraries/jquery-ui-1.10.4.custom.js')
        ->prependFile('common/js/libraries/jquery.min.js')
        ->prependFile('common/js/libraries/modernizr.custom.65934.js')
    }

</head>