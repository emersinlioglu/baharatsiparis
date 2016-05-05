<!-- header -->
<header>

    <!-- top -->
    <div class="top">

        <!-- wrapper -->
        <div class="wrapper">

            {*<h1>*}
                {*{$layoutClientTitle}*}
            {*</h1>*}

            {if $this->userRole}

            <!-- main-header-menu -->
            <div class="main-header-menu">

                <!-- design-select -->
                <div class="design-select default">
                    <div class="wrap">
                        <div class="value">User: {$userName}</div>
                    </div>
                    <ul class="design-select-list hide">
                        <li>
                            <a href="{$userLogoutUrl}">Logout</a>
                        </li>
                    </ul>
                </div>
                <!-- /design-select -->

            </div>
            <!-- main-header-menu -->
            {/if}

        </div>
        <!-- /wrapper -->

    </div>
    <!-- /top -->

    <!-- navi -->
    <div class="navi">

        <!-- wrapper -->
        <div class="wrapper">

            {*<ul class="main-menu">*}
                {*{foreach item=link from=$mainnaviLinks}*}
                    {*<li class="{if $link.active}active{/if}">*}
                        {*<a href="{$link.url}">{$link.title}</a>*}
                    {*</li>*}
                {*{/foreach}*}
            {*</ul>*}

            {*<div class="logos">*}
                {*<div class="outer">*}
                    {*<div class="inner">*}
                        {*{if $logoUrl neq null}*}
                            {*<img class="client-logo {if $logoUrl eq NULL}hide{/if}" src="{$logoUrl}" alt="" />*}
                        {*{/if}*}
                    {*</div>*}
                {*</div>*}
            {*</div>*}
        </div>
        <!-- /wrapper -->

    </div>
    <!-- /navi -->

</header>
<!-- /header -->