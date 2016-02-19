<!-- auth/login.tpl -->
{$script=$this->headScript()->appendFile('backend/js/controller/AuthController.js')}

<!-- container -->
<div class="container">

    <!-- sec-header -->
    <div class="sec-header">

        <h2>{$this->translate('TTL_LOGIN_TITLE')}</h2>

    </div>
    <!-- /sec-header -->

    <!-- form-login -->
    {$this->form()->openTag($form)}

        <div class="columns">

            <div class="column left">
                {include file='../partials/form/input_text.tpl' fieldName='email'}
                {include file='../partials/form/input_password.tpl' fieldName='password'}
                {include file='../partials/form/input_submit.tpl' fieldName='login'}
            </div>

        </div>
    {$this->form()->closeTag()}
    <!-- /form-login -->

</div>
<!-- /container -->

<script>
    AuthController.initLogin();
</script>

<!-- /auth/login.tpl -->