<?php

/**
 * Plugin Name: CC-Redirects
 * Plugin URI:  https://wordpress.org/plugins/cc-redirects
 * Description: This plugin allows you to create simple redirect requests to another page on your site or elsewhere on the web.
 * Version:     1.1.1
 * Author:      Clearcode
 * Author URI:  https://clearcode.cc
 * Text Domain: cc-redirects
 * Domain Path: /languages/
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt

   Copyright (C) 2022 by Clearcode <https://clearcode.cc>
   and associates (see AUTHORS.txt file).

   This file is part of CC-Redirects.

   CC-Redirects is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   CC-Redirects is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with CC-Redirects; if not, write to the Free Software
   Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace Clearcode\Redirects;
use Clearcode\Redirects;
use Exception;

defined( 'ABSPATH' ) or exit;

require __DIR__ . '/vendor/autoload.php';

try {
	Redirects::instance( __FILE__ );
} catch ( Exception $exception ) {
	if ( WP_DEBUG && WP_DEBUG_DISPLAY )
		echo $exception->getMessage();
	error_log( $exception->getMessage() );
}
