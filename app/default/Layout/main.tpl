<!doctype html>
<html lang="en" class="no-js">
    <head>
        {meta}
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <title>PINA</title>
        
        
        {style src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"}{/style}
        {script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"}{/script}
        {script src="https://getbootstrap.com/docs/3.3/dist/js/bootstrap.min.js"}{/script}
        
        {style}
        <style>
            {literal}
                .footer {
                    padding-top: 19px;
                    color: #777;
                    border-top: 1px solid #e5e5e5;
                    margin-top: 40px;
                }
            {/literal}
        </style>
        {/style}
        
        {styles}
    </head>
    <body>

        {include file="Skin/page-header.tpl"}

        <div class="container">

            {$content}

        </div>

        {include file="Skin/page-footer.tpl"}

        {scripts}
    </body>

</html>