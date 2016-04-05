<!-- admin/userform.tpl -->

<!-- form-user -->
{$this->form()->openTag($form)}

    <!-- columns -->
    <div class="columns">

        <div class="column left">
            {include file='../partials/form/input_text.tpl' fieldName='name'}
            {include file='../partials/form/input_text.tpl' fieldName='email'}
            {include file='../partials/form/input_password.tpl' fieldName='newPassword'}
            {include file='../partials/form/input_check.tpl' fieldName='isLocked'}
        </div>

        <div class="column full">

            <h3>{$this->translate('TTL_USER_RIGHTS')}</h3>

            <div class="column left">
                <p>{$this->translate('TTL_USER_RIGHTS_AREAS')}</p>
                {include file='../partials/form/input_check.tpl' fieldName='allowProducts'}
                {include file='../partials/form/input_check.tpl' fieldName='allowAttributes'}
                {include file='../partials/form/input_check.tpl' fieldName='allowTemplates'}
                {include file='../partials/form/input_check.tpl' fieldName='allowAdmin'}
            </div>

            <div class="column right">
                <p>{$this->translate('TTL_USER_RIGHTS_FUNCTIONS')}</p>
                {include file='../partials/form/input_check.tpl' fieldName='allowDelete'}
                {include file='../partials/form/input_check.tpl' fieldName='allowEdit'}
            </div>

        </div>

        <div class="column full buttons">
            {include file='../partials/form/input_submit.tpl' fieldName='send'}
            {if $userExists}
                {include file='../partials/form/input_submit.tpl' fieldName='delete'}
            {/if}
        </div>

    </div>
    <!-- /columns -->

{$this->form()->closeTag()}
<!-- /form-user -->

{literal}
    <script>
    (function(scope) {
        new scope.UserForm($('.form-user:not(.active)').first());
    })(window);
    </script>
{/literal}
<!-- /admin/userform.tpl -->