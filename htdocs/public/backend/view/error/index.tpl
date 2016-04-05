<!-- error/index -->

<h1>{$this->message}</h1>

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

<!-- /error/index -->
