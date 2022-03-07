<?php

namespace Davitig\Ima;

use Illuminate\Support\HtmlString;

class Redirector
{
    /**
     * Create a template with a form that submits to ECOMM.
     *
     * @param  string $clientHandler
     * @param  string|null $transId
     * @param  bool $decode
     * @return \Illuminate\Support\HtmlString
     */
    public static function payment(string $clientHandler, ?string $transId, bool $decode = true): HtmlString
    {
        $html = <<<EOL
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Merchant post to ECOMM</title>
    <style>body {text-align:center;}</style>
    <script type="text/javascript">
        function redirect() {
            document.getElementById("form").submit();
        }
    </script>
</head>
<body onLoad="javascript:redirect()">
    <form action="$clientHandler" id="form" method="POST">
        <input type="hidden" name="trans_id" value="$transId">
        <noscript>
            <p>Please click the submit button below.</p>
            <input type="submit" value="Submit" />
        </noscript>
    </form>
</body>
</html>
EOL;

        return new HtmlString(
            $decode ? html_entity_decode($html, ENT_QUOTES, 'UTF-8') : $html
        );
    }
}
