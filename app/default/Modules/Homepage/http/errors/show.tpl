{content name="page_header"}Error{/content}

<div class="panel">
    <div class="panel-heading">
        <h2>{$error|substr:0:3}</h2>
    </div>
    <div class="panel-body">
        <p>{$error|substr:3|trim|strip_tags|default:"Error"}</p>
    </div>
</div>