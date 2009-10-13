<?php

$books = array(
    array('id' => 1, 'title' => 'The Selfish Gene', 'author' => 'Richard Dawkins', 'publication_year' => '1976'),
    array('id' => 2, 'title' => 'The Wealth of Nations', 'author' => 'Adam Smith', 'publication_year' => '1776'),
    array('id' => 3, 'title' => 'The Prince', 'author' => 'Niccolo Machiavelli', 'publication_year' => '1532'),
    array('id' => 4, 'title' => 'On the Origin of Species', 'author' => 'Charles Darwin', 'publication_year' => '1859'),
    array('id' => 5, 'title' => 'The Revolution: A Manifesto', 'author' => 'Ron Paul', 'publication_year' => '2008'),
);

$response->add_item($books[$query->resource_id - 1]);

$response->total_items = 1;


if ((int) $response->total_items)
{
    $response->status = 200;
}
else
{
    $response->status = 204;
}

?>
