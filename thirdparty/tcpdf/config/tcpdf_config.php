<?php
//============================================================+
// File name   : tcpdf_config.php
// Begin       : 2004-06-11
// Last Update : 2011-04-15
//
// Description : Configuration file for TCPDF.
// Author      : Nicola Asuni - Tecnick.com LTD - Manor Coach House, Church Hill, Aldershot, Hants, GU12 4RQ, UK - www.tecnick.com - info@tecnick.com
// License     : GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
// -------------------------------------------------------------------
// Copyright (C) 2004-2012  Nicola Asuni - Tecnick.com LTD
//
// This file is part of TCPDF software library.
//
// TCPDF is free software: you can redistribute it and/or modify it
// under the terms of the GNU Lesser General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// TCPDF is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with TCPDF.  If not, see <http://www.gnu.org/licenses/>.
//
// See LICENSE.TXT file for more information.
//============================================================+

/**
 * Configuration file for TCPDF.
 * @author Nicola Asuni
 * @package com.tecnick.tcpdf
 * @version 4.9.005
 * @since 2004-10-27
 */

// If you define the constant K_TCPDF_EXTERNAL_CONFIG, the following settings will be ignored.

if (!defined('K_TCPDF_EXTERNAL_CONFIG')) {

	/**
	 * Path of tcpdf library.
	 */
	define ('K_PATH_MAIN', PATH_THIRDPARTY . 'tcpdf' . PATH_SEP);

	/**
	 * Build the URL
	 */
	$protocol = G::is_https() ? "https://" : "http://";
	$host = $_SERVER["SERVER_NAME"] . ($_SERVER["SERVER_PORT"] != "80" ? ":" . $_SERVER["SERVER_PORT"] : "");

	/**
	 * URL path to tcpdf installation folder (http://localhost/tcpdf/).
	 */
	define ('K_PATH_URL', $protocol . $host . "/tcpdf/");

	/**
	 * path for PDF fonts
	 * use K_PATH_MAIN.'fonts/old/' for old non-UTF8 fonts
	 */
	define ('K_PATH_FONTS', K_PATH_MAIN.'fonts/');

	/**
	 * cache directory for temporary files (full path)
	 */
	//define ('K_PATH_CACHE', K_PATH_MAIN.'cache/');
    G::mk_dir(PATH_DATA_REPORTS . 'cache/');
    define ('K_PATH_CACHE', PATH_DATA_REPORTS.'cache/');

	/**
	 * cache directory for temporary files (url path)
	 */
	define ('K_PATH_URL_CACHE', K_PATH_URL.'cache/');

	/**
	 *images directory
	 */
	define ('K_PATH_IMAGES', K_PATH_MAIN.'images/');

	/**
	 * blank image
	 */
	define ('K_BLANK_IMAGE', K_PATH_IMAGES.'_blank.png');

	/**
	 * page format
	 */
	define ('PDF_PAGE_FORMAT', 'A4');

	/**
	 * page orientation (P=portrait, L=landscape)
	 */
	define ('PDF_PAGE_ORIENTATION', 'P');

	/**
	 * document creator
	 */
	define ('PDF_CREATOR', 'TCPDF');

	/**
	 * document author
	 */
	define ('PDF_AUTHOR', 'TCPDF');

	/**
	 * header title
	 */
	define ('PDF_HEADER_TITLE', 'TCPDF Example');

	/**
	 * header description string
	 */
	define ('PDF_HEADER_STRING', "by Nicola Asuni - Tecnick.com\nwww.tcpdf.org");

	/**
	 * image logo
	 */
	define ('PDF_HEADER_LOGO', 'tcpdf_logo.jpg');

	/**
	 * header logo image width [mm]
	 */
	define ('PDF_HEADER_LOGO_WIDTH', 30);

	/**
	 *  document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch]
	 */
	define ('PDF_UNIT', 'mm');

	/**
	 * header margin
	 */
	define ('PDF_MARGIN_HEADER', 5);

	/**
	 * footer margin
	 */
	define ('PDF_MARGIN_FOOTER', 10);

	/**
	 * top margin
	 */
	define ('PDF_MARGIN_TOP', 27);

	/**
	 * bottom margin
	 */
	define ('PDF_MARGIN_BOTTOM', 25);

	/**
	 * left margin
	 */
	define ('PDF_MARGIN_LEFT', 15);

	/**
	 * right margin
	 */
	define ('PDF_MARGIN_RIGHT', 15);

	/**
	 * default main font name
	 */
	//define ('PDF_FONT_NAME_MAIN', 'helvetica');
    define ('PDF_FONT_NAME_MAIN', 'timesnewroman');

	/**
	 * default main font size
	 */
	define ('PDF_FONT_SIZE_MAIN', 10);

	/**
	 * default data font name
	 */
	//define ('PDF_FONT_NAME_DATA', 'helvetica');
    define ('PDF_FONT_NAME_DATA', 'arial');

	/**
	 * default data font size
	 */
	define ('PDF_FONT_SIZE_DATA', 8);

	/**
	 * default monospaced font name
	 */
	define ('PDF_FONT_MONOSPACED', 'courier');

	/**
	 * ratio used to adjust the conversion of pixels to user units
	 */
	define ('PDF_IMAGE_SCALE_RATIO', 1.25);

	/**
	 * magnification factor for titles
	 */
	define('HEAD_MAGNIFICATION', 1.1);

	/**
	 * height of cell respect font height
	 */
	define('K_CELL_HEIGHT_RATIO', 1.25);

	/**
	 * title magnification respect main font size
	 */
	define('K_TITLE_MAGNIFICATION', 1.3);

	/**
	 * reduction factor for small font
	 */
	define('K_SMALL_RATIO', 2/3);

	/**
	 * set to true to enable the special procedure used to avoid the overlappind of symbols on Thai language
	 */
	define('K_THAI_TOPCHARS', true);

	/**
	 * if true allows to call TCPDF methods using HTML syntax
	 * IMPORTANT: For security reason, disable this feature if you are printing user HTML content.
	 */
	define('K_TCPDF_CALLS_IN_HTML', true);
}

//============================================================+
// END OF FILE
//============================================================+
