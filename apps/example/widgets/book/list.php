<table border="1">
    <thead>
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Publication Year</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach (\Steam\Model::retrieve('book') as $book) { ?>
        <tr>
            <td><a href="<?php print \Steam::uri('/book/view?id=' . $book->id) ?>"><?php print $book->title ?></a></td>
            <td><?php print $book->author ?></td>
            <td><?php print $book->publication_year ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
