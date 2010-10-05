<?php

$book = $data[0];

$html = '<h1>' . htmlspecialchars($book->title) . '</h1>';
$html .= '<p>written by ' . $book->author . '<br/>published in' . $book->publication_year . '</p>';
$html .= '<a href="' . Steam_Application::uri('/') . '">back</a>';

return $html;

?>
