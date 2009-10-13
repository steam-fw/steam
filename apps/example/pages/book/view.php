<?php

$webpage = new Steam_Web_Page('default.html');

$webpage->set('title', 'Steam Example Application');

$book_data = Steam_Data::retrieve('book/' . Steam_Web::request('id'));

$book = new Steam_Web_Page_Component('book/view');
$book->data($book_data);
$webpage->insert('content', $book);

$webpage->display();

?>
