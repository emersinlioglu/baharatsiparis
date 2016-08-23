<!-- auth/login.tpl -->
{$script=$this->headScript()->appendFile('js/frontend/AuthController.js')}

<!-- form-login -->
{$this->form()->openTag($form)}

    {*<div class="row">*}

        {*<div class="col-md-4 col-md-offset-4">*}
            {*<h2 class="form-signin-heading">{$this->translate('TTL_LOGIN_TITLE')}</h2>*}
            {*<br />*}
            {*<br />*}
            {include file='../partials/form/input_text.tpl' fieldName='email'}
            {include file='../partials/form/input_password.tpl' fieldName='password'}
            {include file='../partials/form/input_submit.tpl' fieldName='login'}
        {*</div>*}
    {*</div>*}

{$this->form()->closeTag()}
<!-- /form-login -->

<script>
    AuthController.initLogin();
</script>

<!-- /auth/login.tpl -->