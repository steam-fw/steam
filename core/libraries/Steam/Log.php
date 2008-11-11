<?php
/**
 * Steam Log Class
 *
 * This class provides arbitrary message logging services.
 *
 * Copyright 2008-2009 Shaddy Zeineddine
 *
 * This file is part of Steam, a PHP application framework.
 *
 * Steam is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Steam is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category Frameworks
 * @package Steam
 * @copyright 2008-2009 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */

class Steam_Log
{
    /**
     * Records a message for the referenced item within the specified context
     * at the specified level of verbosity.
     *
     * @return void
     * @param string $message_context message context
     * @param mixed $identifier reference identifier
     * @param string $message log message
     * @param int $verbosity level of detail
     */
    public function write($message_context, $identifier, $message, $verbosity = 0)
    {
        Steam_Db::write()->query('INSERT INTO message_log (message_context, identifier, message, message_timestamp, user_id, verbosity) VALUES(\'' . Steam_Db::write()->escape($message_context) . '\', \'' . Steam_Db::write()->escape($identifier) . '\', \'' . Steam_Db::write()->escape($message) . '\', NOW(), \'' . $_SESSION['Steam_user_id'] . '\', \'' . Steam_Db::write()->escape($verbosity) . '\')');
    }
    
    /**
     * Retrieves an array of arrays of the relevant log messages at the
     * specified verbosity.
     *
     * @return array
     * @param string $message_context message context
     * @param mixed $identifier reference identifier
     * @param int $verbosity level of detail
     */
    public function read($message_context, $identifier, $verbosity = NULL)
    {
        $verbosity_condition = (is_int($verbosity)) ? ' AND log_level > \'' . $verbosity . '\'' : '';
        
        Steam_Db::read()->select_rows('SELECT message, message_timestamp, user_id FROM message_log WHERE message_context = \'' . Steam_Db::write()->escape($message_context) . '\' AND identifier = \'' . Steam_Db::write()->escape($identifier) . '\'' . $verbosity_condition);
    }
}

?>
