{$this->doctype()}
<html lang="de">

{include file="./partials/head.tpl"}

<body{if $layoutClass} class="{$layoutClass}"{/if}>

    {include file="./partials/header.tpl"}

    <!-- content -->
    <section>

        <!-- panemanager -->
        <div class="panemanager one-pane">

            <!-- panemanager-pane -->
            <div class="panemanager-pane panemanager-pane-main loaded">

                <h2>
                    {$this->translate($paneFirstTitle)}
                </h2>

                <!-- panemanager-pane-content -->
                <div class="panemanager-pane-content">

                    <!-- panemanager-pane-scrollpane -->
                    <div class="panemanager-pane-scrollpane">

                        {$this->content}

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