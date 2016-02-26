{$this->doctype()}

<!-- layout of TMS backend -->

<html lang="de">

{include file="./partials/head.tpl"}

<style>
    pre {
        background-color: ghostwhite;
        border: 1px solid silver;
        padding: 10px 20px;
        margin: 20px;
    }
    .json-key {
        color: brown;
    }
    .json-value {
        color: navy;
    }
    .json-string {
        color: olive;
    }
</style>

<body{if $layoutClass} class="{$layoutClass}"{/if} style="overflow: auto;">

    {include file="./partials/header.tpl"}

    <!-- content -->
    <section style="margin: 0 auto; width: 1280px; padding: 43px 0 0 36px;">{$this->content}</section>
    <!-- /content -->

</body>

</html>