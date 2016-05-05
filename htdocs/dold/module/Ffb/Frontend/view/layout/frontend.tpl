{$this->doctype()}

<!-- layout of frontend -->

<html lang="de">

{include file="./partials/head.tpl"}

<body{if $layoutClass} class="{$layoutClass}"{/if}>

    {*{include file="./partials/header.tpl"}*}

    <!-- container -->
    <section class="container">

        {$this->content}

    </section>
    <!-- /container -->

    {include file="./partials/footer_scripts.tpl"}

</body>

</html>