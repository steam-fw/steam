<?php

$html = '<table border="1">';
$html .= '<thead><tr><th>Title</th><th>Author</th><th>Publication Year</th></tr></thead><tbody>';

foreach ($data as $book)
{
    $html .= '<tr><td><a href="' . Steam_Application::uri('book/view?id=' . $book->id) . '">' . $book->title . '</a></td><td>' . $book->author . '</td><td>' . $book->publication_year . '</td></tr>';
}

return $html . '</tbody></table>';

?>
