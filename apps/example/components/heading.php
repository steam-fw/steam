<?php

$text = (isset($options['text'])) ? $options['text'] : '';

return '<h1>' . htmlspecialchars($text) . '</h1>';

?>
