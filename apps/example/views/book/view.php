<?php

$template = 'standard';

$books = \Steam\Model::retrieve('book?id=' . $_GET['id']);

$text   = array();
$layout = array();

if ((int) $books->status == 200)
{
    $text['title']       = $books[0]->title;
    $layout['content'][] = 'book/view';
}
else
{
    $text['title']       = 'Book Not Found';
}
?>
