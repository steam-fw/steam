<?php

class BookModel extends \Steam\Model
{
    private static $books = array(
            1 => array('id' => 1, 'title' => 'The Selfish Gene', 'author' => 'Richard Dawkins', 'publication_year' => '1976'),
            2 => array('id' => 2, 'title' => 'The Wealth of Nations', 'author' => 'Adam Smith', 'publication_year' => '1776'),
            3 => array('id' => 3, 'title' => 'The Prince', 'author' => 'Niccolo Machiavelli', 'publication_year' => '1532'),
            4 => array('id' => 4, 'title' => 'On the Origin of Species', 'author' => 'Charles Darwin', 'publication_year' => '1859'),
            5 => array('id' => 5, 'title' => 'The Revolution: A Manifesto', 'author' => 'Ron Paul', 'publication_year' => '2008'),
        );
    
    protected static function _retrieve(\Steam\Model\Query &$query, \Steam\Model\Response &$response)
    {
        $parameters = http_parse_query((string) $query->parameters);
        
        if (isset($parameters['id']))
        {
            if (isset(self::$books[$parameters['id']]))
            {
                $response->add_item(self::$books[$parameters['id']]);
            }
        }
        else
        {
            $response->add_items(self::$books);
        }

        if ((int) $response->total_items)
        {
            $response->status = 200;
        }
        else
        {
            $response->status = 204;
        }
        
        /*
        // Sample MySQL query
        
        $select = \Steam\Db::read()->select()
            ->from(array('b' => 'books'));
        
        $parameters = http_parse_query((string) $query->parameters);
        
        foreach ($parameters as $field => $value)
        {
            if ($field == 'book_id')
            {
                $parameters['b.book_id'] = $value;
                unset($parameters['book_id']);
            }
        }
        
        $query->parameters = http_build_query($parameters);
        
        $sql = new \Steam\Model\SQL($query, $response);
        $sql->key('b.book_id');
        $sql->retrieve($select);
        */
    }
}

?>
