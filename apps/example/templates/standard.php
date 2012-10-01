<?php print '<?xml version="1.0" encoding="utf-8"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php print $title ?></title>
        <link href="<?php print \Steam\StaticResource::uri('/css/default.css') ?>" rel="stylesheet" type="text/css" media="all"/>
    </head>
    <body>
        <h1><?php print $title ?></h1>
        <?php print $content ?>
    </body>
</html>
