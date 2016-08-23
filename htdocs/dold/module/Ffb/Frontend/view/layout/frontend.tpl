{$this->doctype()}

<!-- layout of frontend -->

<html lang="de">

{include file="./partials/head.tpl"}

<body{if $layoutClass} class="{$layoutClass}"{/if}>

    {*{include file="./partials/header.tpl"}*}

    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
{*                <a class="navbar-brand" href="#">Firma</a>*}
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li class="active"><a href="#">Produkte</a></li>
{*                    <li><a href="#about">Übersicht</a></li>*}
{*                    <li><a href="#contact">Bestätigen</a></li>*}
                </ul>
                <ul class="nav navbar-nav pull-right">
                    {*<li class="active"><a href="#">Home</a></li>*}
                    {*<li><a href="#about">About</a></li>*}
                    {*<li><a href="#contact">Contact</a></li>*}
                    <li class="dropdown presentation">
                        <a href="#"
                           class="dropdown-toggle"
                           data-toggle="dropdown"
                           role="button"
                           aria-haspopup="true"
                           aria-expanded="false">
                            Warenkorb
                            <span class="badge">42,00 €</span>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <table class="table">
                                    <tr>
                                        <td>Anis</td>
                                        <td>3x</td>
                                        <td>5,60 €</td>
                                    </tr>
                                    <tr>
                                        <td>Anis</td>
                                        <td>3x</td>
                                        <td>5,60 €</td>
                                    </tr>
                                    <tr>
                                        <td>Anis</td>
                                        <td>3x</td>
                                        <td>5,60 €</td>
                                    </tr>
                                    <tr>
                                        <td>Anis</td>
                                        <td>3x</td>
                                        <td>5,60 €</td>
                                    </tr>
                                    <tr>
                                        <td>Anis</td>
                                        <td>3x</td>
                                        <td>5,60 €</td>
                                    </tr>
                                    <tr>
                                        <td>Anis</td>
                                        <td>3x</td>
                                        <td>5,60 €</td>
                                    </tr>
                                    <tr>
                                        <td>Anis</td>
                                        <td>3x</td>
                                        <td>5,60 €</td>
                                    </tr>
                                    <tr>
                                        <td>Anis</td>
                                        <td>3x</td>
                                        <td>5,60 €</td>
                                    </tr>
                                </table>
                            </li>
                            {*<li class="dropdown-header">Nav header</li>*}
                            {*<li role="separator" class="divider"></li>*}
                        </ul>
                    </li>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </nav>

    <!-- container -->
{*    <section class="container">*}

        {$this->content}

{*    </section>*}
    <!-- /container -->

    {include file="./partials/footer_scripts.tpl"}

</body>

</html>