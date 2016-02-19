{$this->doctype()}
<html lang="de">

{include file="./partials/head.tpl"}

<body{if $layoutClass} class="{$layoutClass}"{/if}>

    {include file="./partials/header.tpl"}

    <!-- content -->
    <section>

        {$this->content}

    </section>
    <!-- /content -->

    {include file="./partials/footer_scripts.tpl"}

</body>

</html>