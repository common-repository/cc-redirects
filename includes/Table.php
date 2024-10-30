<?php

/*
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
use WP_List_Table;

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( __NAMESPACE__ . '\Table' ) ) {
	class Table extends WP_List_Table {
		protected $redirects = [];

		public function __construct( $redirects ) {
			if ( ! empty( $redirects ) and is_array( $redirects ) ) {
				array_walk( $redirects, function( &$value, $key ) {
					$value['id'] = $key;
				} );
				$this->redirects = array_reverse( $redirects, true );
			}

			parent::__construct( [
				'singular' => Redirects::get( 'slug' ),
				'plural'   => Redirects::get( 'slug' ),
				'ajax'     => false
			] );
		}

		public function get_columns() {
			return [
				'id'         => Redirects::__( '#'          ),
				'from'       => Redirects::__( 'From'       ),
				'to'         => Redirects::__( 'To'         ),
                'comparison' => Redirects::__( 'Comparison' ),
				'parameters' => Redirects::__( 'Parameters' ),
				'code'       => Redirects::__( 'Code'       )
			];
		}

		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'id' : return ++$item['id'];
				case 'from' :
                    $actions['edit'] = Redirects::render( 'button', [
                        'type'  => 'submit',
                        'class' => 'button-link',
                        'content' => Redirects::__( 'Save' )
                    ] );
                    if ( count( $this->redirects ) !== $item['id'] + 1 )
                        $actions['delete'] = Redirects::render( 'link', [
                            'link'  => Redirects::__( 'Remove' ),
                            'class' => '',
                            'url'   => wp_nonce_url( add_query_arg( [
                                'action' => 'remove',
                                'id'     => $item['id'],
                            ], admin_url( sprintf( Settings::URL, Redirects::get( 'slug' ) ) ) ), 'remove' )
                        ] );
				case 'to' :
					return Settings::input( [
						'type'  => 'text',
						'name'  => Redirects::get( 'slug' ) . '[' . $item['id'] . '][' . $column_name . ']',
						'value' => Settings::sanitize_url( $item[$column_name] ),
					] ) . $this->row_actions( $actions ?? [] );
                case 'comparison' :
                    return Settings::select( [
                        'name'     => Redirects::get( 'slug' ) . '[' . $item['id'] . '][comparison]',
                        'selected' => Settings::sanitize_comparison( $item['comparison'] ?? 'path' ),
                        'options'  => [
                            'path'  => Redirects::__( 'Ignore parameters' ),
                            'query' => Redirects::__( 'Precise address'   )
                        ]
                    ] );
				case 'parameters' :
                    return Settings::select( [
                        'name'     => Redirects::get( 'slug' ) . '[' . $item['id'] . '][parameters]',
                        'selected' => (int)Settings::sanitize_parameters( $item['parameters'] ?? true ),
                        'options'  => [
                            1 => Redirects::__( 'Forward' ),
                            0 => Redirects::__( 'Remove'  )
                        ]
                    ] );
				case 'code' :
					return Settings::select( [
						'name'     => Redirects::get( 'slug' ) . '[' . $item['id'] . '][code]',
						'selected' => Settings::sanitize_code( $item['code'] ?? 301 ),
						'options'  => [
							301  => '301',
							302  => '302'
						]
					] );
				default : return print_r( $item, true );
			}
		}

		public function no_items() {
			echo Redirects::__( 'No redirects available.' );
		}

		public function get_items( $per_page = 100, $page_number = 1 ) {
			$offset = ( $page_number - 1 ) * $per_page;
			return array_slice( $this->redirects, $offset, $per_page );
		}

		public function count_items() {
			return count( $this->redirects );
		}

		public function prepare_items() {
			$per_page     = $this->get_items_per_page( 'redirects_per_page', 100 );
			$current_page = $this->get_pagenum();
			$total_items  = $this->count_items();

			$this->set_pagination_args( [
				'total_items' => $total_items,
				'per_page'    => $per_page
			] );

			$columns  = $this->get_columns();
			$hidden   = [];
			$sortable = [];
			$this->_column_headers = [ $columns, $hidden, $sortable ];

			$this->items = $this->get_items( $per_page, $current_page );
		}
	}
}
