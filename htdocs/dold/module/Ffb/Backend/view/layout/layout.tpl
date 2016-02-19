{$this->doctype()}

<!-- layout of TMS backend -->

<html lang="de">

{include file="./partials/head.tpl"}

<body{if $layoutClass} class="{$layoutClass}"{/if}>

    {include file="./partials/header.tpl"}

    <!-- content -->
    <section>

        <!-- panemanager -->
        <div class="panemanager{if $withSubnavi eq true} with-subnavi{/if}">

            <!-- panemanager-pane -->
            <div class="panemanager-pane main-navi-pane loaded">

                {if $languages}
                <ul class="linked-list-lang-switcher">
                    {foreach from=$languages item=lang}
                    <li class="{if $contentLang == $lang->getIso()}selected{/if}"
                        data-iso="{$lang->getIso()}"
                        data-id="{$lang->getId()}">
                        {$lang->getName()}
                    </li>
                    {/foreach}
                </ul>
                {/if}

                <h2>
                    {$this->translate($paneFirstTitle)}
                </h2>

                <!-- panemanager-pane-content -->
                <div class="panemanager-pane-content">

                    {$this->content}

                </div>
                <!-- /panemanager-pane-content -->

            </div>
            <!-- /panemanager-pane -->

            <!-- panemanager-pane -->
            <div class="panemanager-pane sub-navi-pane loaded">

                <h2 class="toggle">
                    {$this->translate($paneSecondTitle)}
                </h2>

                <!-- panemanager-pane-content -->
                <div class="panemanager-pane-content">
                    {$paneSecondContent}
                </div>
                <!-- /panemanager-pane-content -->
            </div>
            <!-- /panemanager-pane -->

            <!-- panemanager-pane -->
            <div class="panemanager-pane  panemanager-pane-main{if $mainSectionContent} show{/if}">

                <h2 class="toggle hide">
                    &nbsp;
                </h2>

                <!-- panemanager-pane-content -->
                <div class="panemanager-pane-content">

                    <!-- panemanager-pane-scrollpane -->
                    <div class="panemanager-pane-scrollpane">

                        {$mainSectionContent}

                    </div>
                    <!-- /panemanager-pane-scrollpane -->

                </div>
                <!-- /panemanager-pane-content -->

            </div>
            <!-- /panemanager-pane -->

        </div>
        <!-- /panemanager -->

    </section>
    <!-- /content -->

    {include file="./partials/footer_scripts.tpl"}

</body>

</html>