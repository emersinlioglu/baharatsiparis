{$layout=$this->layout()->setTemplate('layout/layout_empty')}

<div class="page-404">
    
    <div class="inner">

        <p class="error-code">
            <span class="code">404</span>
            Error
        </p>

        <div class="info">
            <p>Die von ihnen angeforderte Seite wurde nicht gefunden</p>
        </div>

        <a href="#">
            <span class="arrow"></span>
            Hier gehts zurück zur Hauptseite
        </a>

    </div>

</div>

<!--

<h1>A 404 error occured</h1>
<h2>{$this->message}</h2>

{if (isset($this->reason) && $this->reason)}
    {if $this->reason == 'error-controller-cannot-dispatch'}
        <p>The requested controller was unable to dispatch the request.</p>
    {elseif $this->reason == 'error-controller-not-found'}
        <p>The requested controller could not be mapped to an existing controller class.</p>
    {elseif $this->reason == 'error-controller-invalid'}
        <p>The requested controller was not dispatchable.</p>
    {elseif $this->reason == 'error-router-no-match'}
        <p>The requested URL could not be matched by routing.</p>
    {else}
        <p>We cannot determine at this time why a 404 was generated.</p>
    {/if}
{/if}

{if (isset($this->controller) && $this->controller)}
    <dl>
        <dt>Controller:</dt>
        <dd>{$this->escapeHtml($this->controller)}

            {if isset($this->controller_class) && $this->controller_class && $this->controller_class != $this->controller}
                &nbsp;(resolves to {$this->escapeHtml($this->controller_class)})
            {/if}
        </dd>
    </dl>
{/if}

{if (isset($this->display_exceptions) && $this->display_exceptions)}
    {if isset($this->exception) && $this->exception instanceof Exception}
        <hr/>
        <h2>Additional information</h2>
        <h3>{get_class($this->exception)}</h3>
        <dl>
            <dt>File:</dt>
            <dd>
                <pre class="prettyprint linenums">{$this->exception->getFile()}:{$this->exception->getLine()}</pre>
            </dd>
            <dt>Message:</dt>
            <dd>
                <pre class="prettyprint linenums">{$this->exception->getMessage()}</pre>
            </dd>
            <dt>Stack trace:</dt>
            <dd>
                <pre class="prettyprint linenums">{$this->exception->getTraceAsString()}</pre>
            </dd>
        </dl>
    {else}
        <h3>No Exception available</h3>
    {/if}
{/if}

-->