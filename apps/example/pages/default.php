<?php

$webpage = new Steam_Web_Page('default.html');

$webpage->set('title', 'Steam Example Application');

$heading = new Steam_Web_Page_Component('heading');
$heading->option('text', 'Steam Example Application');
$webpage->insert('content', $heading);


$book_data = Steam_Data::retrieve('books');

$list = new Steam_Web_Page_Component('book/list');
$list->data($book_data);
$webpage->insert('content', $list);

$webpage->display();

?>
