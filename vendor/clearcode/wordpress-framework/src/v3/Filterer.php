<?php

/*
    Copyright (C) 2022 by Clearcode <https://clearcode.cc>
    and associates (see AUTHORS.txt file).

    This file is part of clearcode/wordpress-framework.

    clearcode/wordpress-framework is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    clearcode/wordpress-framework is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with clearcode/wordpress-framework; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace Clearcode\Redirects\Vendor\Clearcode\Framework\v3;

use ReflectionClass;
use ReflectionMethod;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( __NAMESPACE__ . '\Filterer' ) ) {
    class Filterer {
        public function __construct( $var ) {
            $class   = new ReflectionClass( $var );
            $methods = is_object( $var ) ?
                $class->getMethods( ReflectionMethod::IS_PUBLIC ) :
                $class->getMethods( ReflectionMethod::IS_PUBLIC & ReflectionMethod::IS_STATIC );

            foreach ( $methods as $method ) {
                if ( (bool)$this->is_filter( $method->getName() ) ) {
                    $filter   = $this->get_filter( $method->getName() );
                    $priority = $this->get_priority( $method->getName() );
                    $args     = $method->getNumberOfParameters();

                    add_filter( $filter, [ $var, $method->getName() ], $priority, $args );
                }
            }
        }

        protected function get_priority( $method ) {
            $priority = substr( strrchr( $method, '_' ), 1 );

            return is_numeric( $priority ) ? (int)$priority : 10;
        }

        protected function has_priority( $method ) {
            $priority = substr( strrchr( $method, '_' ), 1 );

            return is_numeric( $priority ) ? true : false;
        }

        protected function get_filter( $method ) {
            if ( $this->has_priority( $method ) ) {
                $method = substr( $method, 0, strlen( $method ) - strlen( $this->get_priority( $method ) ) - 1 );
            }
            if ( $filter = $this->is_filter( $method ) ) {
                $method = substr( $method, strlen( $filter ) + 1 );
            }

            return $method;
        }

        protected function is_filter( $method ) {
            foreach ( [ 'filter', 'action' ] as $filter ) {
                if ( 0 === strpos( $method, $filter . '_' ) ) {
                    return $filter;
                }
            }

            return false;
        }
    }
}
