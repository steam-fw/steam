<?php

$book = \Steam\Model::retrieve('book?id=' . $_GET['id']);
$book = $book[0];

?>
<p>written by <?php print $book->author ?><br/>published in <?php print $book->publication_year ?></p>
<a href="<?php print \Steam::uri('/') ?>">back</a>
