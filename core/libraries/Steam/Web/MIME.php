<?php
/**
 * Steam URI Class
 *
 * This class contains utilities for determining MIME types.
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

class Steam_Web_MIME
{
    protected $mime_types = '
application/andrew-inset ez
application/mac-binhex40 hqx
application/mac-compactpro cpt
application/mathml+xml mathml
application/msword doc
application/octet-stream bin dms lha lzh exe class so dll
application/oda oda
application/ogg ogg ogm
application/pdf pdf
application/postscript ai eps ps
application/rdf+xml rdf
application/smil smi smil
application/srgs gram
application/srgs+xml grxml
application/vnd.mif mif
application/vnd.ms-excel xls
application/vnd.ms-powerpoint ppt
application/vnd.wap.wbxml wbxml
application/vnd.wap.wmlc wmlc
application/vnd.wap.wmlscriptc wmlsc
application/voicexml+xml vxml
application/x-bcpio bcpio
application/x-bzip gz bz2
application/x-cdlink vcd
application/x-chess-pgn pgn
application/x-cpio cpio
application/x-csh csh
application/x-director dcr dir dxr
application/x-dvi dvi
application/x-futuresplash spl
application/x-gtar gtar tar
application/x-gzip gz
application/x-hdf hdf
application/x-jar jar
application/x-javascript js
application/x-koan skp skd skt skm
application/x-latex latex
application/x-netcdf nc cdf
application/x-sh sh
application/x-shar shar
application/x-shockwave-flash swf
application/x-stuffit sit
application/x-sv4cpio sv4cpio
application/x-sv4crc sv4crc
application/x-tar tar
application/x-tcl tcl
application/x-tex tex
application/x-texinfo texinfo texi
application/x-troff t tr roff
application/x-troff-man man
application/x-troff-me me
application/x-troff-ms ms
application/x-ustar ustar
application/x-wais-source src
application/x-xpinstall xpi
application/xhtml+xml xhtml xht
application/xslt+xml xslt
application/xml xml xsl
application/xml-dtd dtd
application/zip zip jar xpi  sxc stc  sxd std   sxi sti   sxm stm   sxw stw
audio/basic au snd
audio/midi mid midi kar
audio/mpeg mpga mp2 mp3
audio/ogg ogg
audio/x-aiff aif aiff aifc
audio/x-mpegurl m3u
audio/x-ogg ogg
audio/x-pn-realaudio ram rm
audio/x-pn-realaudio-plugin rpm
audio/x-realaudio ra
audio/x-wav wav
chemical/x-pdb pdb
chemical/x-xyz xyz
image/bmp bmp
image/cgm cgm
image/gif gif
image/ief ief
image/jpeg jpeg jpg jpe
image/png png
image/svg+xml svg
image/tiff tiff tif
image/vnd.djvu djvu djv
image/vnd.wap.wbmp wbmp
image/x-cmu-raster ras
image/x-icon ico
image/x-portable-anymap pnm
image/x-portable-bitmap pbm
image/x-portable-graymap pgm
image/x-portable-pixmap ppm
image/x-rgb rgb
image/x-photoshop psd
image/x-xbitmap xbm
image/x-xpixmap xpm
image/x-xwindowdump xwd
model/iges igs iges
model/mesh msh mesh silo
model/vrml wrl vrml
text/calendar ics ifb
text/css css
text/html html htm
text/plain txt
text/richtext rtx
text/rtf rtf
text/sgml sgml sgm
text/tab-separated-values tsv
text/vnd.wap.wml wml
text/vnd.wap.wmlscript wmls
text/xml xml xsl xslt rss rdf
text/x-setext etx
video/mpeg mpeg mpg mpe
video/ogg ogm ogg
video/quicktime qt mov
video/vnd.mpegurl mxu
video/x-msvideo avi
video/x-ogg ogm ogg
video/x-sgi-movie movie
x-conference/x-cooltalk ice
';
    private static $instance;
    
    /**
     * Creates a new instance of Steam_Web_MIME.
     *
     * @return object
     */
    public static function construct()
    {
        if (!isset(self::$instance))
        {
            $class = __CLASS__;
            
            self::$instance = new $class;
        }
        
        return self::$instance;
    }
    
    /**
     * This class can only be instantiated using the construct method.
     *
     * @return void
     */
    private function __construct()
    {
    }
    
    /**
     * This class cannot be cloned.
     *
     * @throws Steam_Exception_General when cloning is attempted
     * @return void
     */
    public function __clone()
    {
        throw Steam::_('Exception', 'General');
    }

    /**
     * This method determines the files MIME type from the file's extension. If
     * it cannot locate a match, it returns false.
     *
     * @return bool|string
     * @param string $file file path or name
     */
    public function get_type($file)
    {
        if (preg_match('/.+\\.([^\\.]+)$/', $file, $extension))
        {
            if (preg_match('/(\\S+)( \\S+)* ' . $extension[1] . '( \\S+)*/', $this->mime_types, $mime_type))
            {
                return $mime_type[1];
            }
        }
        
        return false;
    }
}
?>
