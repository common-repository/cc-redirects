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

use Clearcode\Redirects\Vendor\Clearcode\Framework\v3\Singleton;
use Clearcode\Redirects\Vendor\Clearcode\Framework\v3\Filterer;

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( __NAMESPACE__ . '\Settings' ) ) {
	class Settings {
		use Singleton;

		const URL = 'options-general.php?page=%s';

		protected $page       = 'settings_page_cc-redirects';
		protected $capability = 'manage_options';
		protected $redirects  = [];

		public function __get( $name ) {
			return property_exists( $this, $name ) ? $this->$name : false;
		}

		protected function __construct() {
			new Filterer( $this );

			$this->redirects = (array)get_option( Redirects::get( 'slug' ), [] );

			foreach( array_keys( get_object_vars( $this ) ) as $var )
				$this->$var = Redirects::apply_filters( $var, $this->$var );

			add_filter( 'set-screen-option', function( $status, $option, $value ) {
				$fixed_inputs = [
					'_wpnonce',
					'_wp_http_referer',
					'paged',
					'cc-redirects',
					'action',
					's'
				];
				$dynamic_inputs = 3;
				$max_value = (int)( (int)ini_get( 'max_input_vars' ) / $dynamic_inputs - count( $fixed_inputs ) - $dynamic_inputs );

				return 'redirects_per_page' == $option ? ( $max_value > (int)$value ? (int)$value : $max_value ) : 100;
			}, 10, 3 );
		}

		public function action_admin_menu_999() {
			$hook = add_options_page(
				Redirects::get( 'name' ),
				Redirects::render( 'menu', [
					'class'   => 'dashicons-before dashicons-migrate',
					'content' => Redirects::__( 'Redirects' )
				] ),
				$this->capability,
				Redirects::get( 'slug' ),
				[ $this, 'page' ]
			);

			add_action( "load-$hook", function() {
                add_screen_option( 'per_page', [
					'label'   => Redirects::__( 'Redirects per page' ),
					'default' => 100,
					'option'  => 'redirects_per_page'
				] );

                get_current_screen()->add_help_tab( [
                    'id' => 'redirects',
                    'title' => Redirects::__( 'Redirects' ),
                    'content' => Redirects::render( 'paragraph', [ 'content' =>
                        sprintf(
                            Redirects::__( 'The %s field should be relative to your website root.' ),
                            Redirects::render( 'code', [ 'content' => Redirects::__( 'From' ) ] )
                        ) . '</br>' .
                        sprintf(
                            Redirects::__( 'The %s field can be either a full URL to any page on the web, or relative to your website root.' ),
                            Redirects::render( 'code', [ 'content' => Redirects::__( 'To'   ) ] )
                        ) . '</br>' .
                        Redirects::__( 'From' ) . ': ' . Redirects::render( 'code', [ 'content' => '/old-page/'                   ] ) . ' '     .
                        Redirects::__( 'To'   ) . ': ' . Redirects::render( 'code', [ 'content' => '/new-page/'                   ] ) . '<br/>' .
                        Redirects::__( 'From' ) . ': ' . Redirects::render( 'code', [ 'content' => '/old-page/'                   ] ) . ' '     .
                        Redirects::__( 'To'   ) . ': ' . Redirects::render( 'code', [ 'content' => 'http://example.com/new-page/' ] ) . '<br/>' .
                        '</br>' .
                        sprintf(
                            Redirects::__( 'To use wildcards, put an asterisk %s after the folder name you want to redirect.' ),
                            Redirects::render( 'code', [ 'content' => '*' ] )
                        ) . '</br>' .
                        Redirects::__( 'From' ) . ': ' . Redirects::render( 'code', [ 'content' => '/old-folder/*'              ] ) . ' '     .
                        Redirects::__( 'To'   ) . ': ' . Redirects::render( 'code', [ 'content' => '/redirect-everything-here/' ] ) . '<br/>' .
                        '</br>' .
                        sprintf(
                            Redirects::__( 'You can also use the asterisk %s in the %s field to replace whatever it matches in the %s field.' ),
                            Redirects::render( 'code', [ 'content' => '*'                       ] ),
                            Redirects::render( 'code', [ 'content' => Redirects::__( 'To'     ) ] ),
                            Redirects::render( 'code', [ 'content' => Redirects::__( 'From'   ) ] )
                        ) . '<br/>' .
                        Redirects::__( 'From' ) . ': ' . Redirects::render( 'code', [ 'content' => '/old-folder/*'          ] ) . ' '     .
                        Redirects::__( 'To'   ) . ': ' . Redirects::render( 'code', [ 'content' => '/some/other/folder/*'   ] ) . '<br/>' .
                        Redirects::__( 'From' ) . ': ' . Redirects::render( 'code', [ 'content' => '/old-folder/*/content/' ] ) . ' '     .
                        Redirects::__( 'To'   ) . ': ' . Redirects::render( 'code', [ 'content' => '/some/other/folder/*'   ] ) . '<br/>'
                    ] )
                ] );

                get_current_screen()->add_help_tab( [
                    'id' => 'import',
                    'title' => Redirects::__( 'Import' ),
                    'content' => Redirects::render( 'paragraph', [ 'content' =>
                        sprintf(
                            Redirects::__( 'Format: %s' ),
                            Redirects::render( 'code', [ 'content' => '"from","to"[,"path|query"][,0|1][,301|302]' ] )
                        ) . '</br>' . '</br>' .
                        sprintf(
                            Redirects::__( 'Field delimiter character: %s' ),
                            Redirects::render( 'code', [ 'content' => ',' ] )
                        ) . '</br>' .
                        sprintf(
                            Redirects::__( 'Field enclosure character: %s' ),
                            Redirects::render( 'code', [ 'content' => '"' ] )
                        ) . '</br>' .
                        sprintf(
                            Redirects::__( 'Escape character: %s' ),
                            Redirects::render( 'code', [ 'content' => '\\' ] )
                        ) . '</br>'
                    ] )
                ] );
			} );
		}

		public function action_current_screen( $current_screen ) {
			if ( $this->page === $current_screen->id and $action = $this->get_action() ) $this->$action();
		}

		public function action_admin_enqueue_scripts( $page ) {
			if ( $this->page !== $page ) return;

			wp_register_style( Redirects::get( 'slug' ), Redirects::get( 'url' ) . 'assets/css/style.css', [], Redirects::get( 'version' ) );
			wp_enqueue_style(  Redirects::get( 'slug' ) );

            wp_register_script( Redirects::get( 'slug' ), Redirects::get( 'url' ) . 'assets/js/script.js', [], Redirects::get( 'version' ) );
            wp_enqueue_script(  Redirects::get( 'slug' ) );
		}

		public function page() {
			if ( ! current_user_can( $this->capability ) ) wp_die( Redirects::__( 'Cheatin&#8217; uh?' ) );
			$tab = $this->get_tab();
			$this->$tab();
		}

		protected function redirects() {
            $id = $this->get_id();
            $redirects = false !== $id ? [ $id => $this->redirects[$id] ] : $this->redirects;

			if ( 'search' !== $this->get_action() and false === $id ) $redirects[] = [ 'from' => '', 'to' => '', 'comparison' => 'path', 'parameters' => '1', 'code' => '301' ];

			$table = new Table( $redirects );
			$table->prepare_items();

			$action = 'save';
			$input = self::input( [
					'type'  => 'hidden',
					'name'  => 'action',
					'value' => $action
			] );

			echo Redirects::render( 'redirects', [
				'header' => Redirects::__( 'Redirects' ),
				'id'     => Redirects::get( 'slug' ),
				'table'  => $table,
				'url'    => sprintf( self::URL, Redirects::get( 'slug' ) ),
				'action' => $action,
				'input'  => $input,
				'submit' => Redirects::__( 'Save' ),
				'search' => Redirects::__( 'Search' ),
				's'      => $this->get_search(),
				'result' => Redirects::__( 'Search results for' ),
				'tabs'   => $this->get_tabs(),
				'tab'    => $this->get_tab(),
				'desc'   => Redirects::render( 'button', [
                    'type' => 'button',
                    'id' => Redirects::get( 'slug' ) . '-help-button',
                    'class' => 'button',
                    'content' => Redirects::__( 'Help' )
                ] )
			] );
		}

		protected function import() {
			$action = 'parse';
			$input = self::input( [
				'type'  => 'hidden',
				'name'  => 'action',
				'value' => $action
			] );

			echo Redirects::render( 'import', [
				'header' => Redirects::__( 'Redirects' ),
				'id'     => Redirects::get( 'slug' ),
				'url'    => sprintf( self::URL, Redirects::get( 'slug' ) ),
				'action' => $action,
				'input'  => $input,
				'submit' => Redirects::__( 'Save' ),
				'tabs'   => $this->get_tabs(),
				'tab'    => $this->get_tab(),
				'desc'   => Redirects::__( 'Parse CSV formatted string to redirects.' )
			] );
		}

		protected function export() {
			$export = '';
			foreach ( $this->redirects as $redirect )
				$export .= sprintf(
                    '"%s","%s","%s",%d,%d',
                    $redirect['from'],
                    $redirect['to'],
                    self::sanitize_comparison( $redirect['comparison'] ?? 'path' ),
                    (int)self::sanitize_parameters( $redirect['parameters'] ?? true ),
                    (int)self::sanitize_code( $redirect['code'] ?? 301 )
                ) . "\n";

			echo Redirects::render( 'export', [
				'header' => Redirects::__( 'Redirects' ),
				'id'     => Redirects::get( 'slug' ),
				'url'    => sprintf( self::URL, Redirects::get( 'slug' ) ),
				'tabs'   => $this->get_tabs(),
				'tab'    => $this->get_tab(),
				'export' => $export,
				'desc'   => Redirects::__( 'Exported CSV formatted string of redirects.' )
			] );
		}

		protected function reset() {
			$action = 'clear';
			$input = self::input( [
				'type'  => 'hidden',
				'name'  => 'action',
				'value' => $action
			] );

			echo Redirects::render( 'reset', [
				'header' => Redirects::__( 'Redirects' ),
				'id'     => Redirects::get( 'slug' ),
				'url'    => sprintf( self::URL, Redirects::get( 'slug' ) ),
				'action' => $action,
				'input'  => $input,
				'submit' => Redirects::__( 'Reset' ),
				'tabs'   => $this->get_tabs(),
				'tab'    => $this->get_tab(),
				'desc'   => Redirects::__( 'Remove all redirects.' )
			] );
		}

		protected function get_tab() {
			return ( isset( $_GET['tab'] ) and in_array( $_GET['tab'], [ 'import', 'export', 'reset' ] ) ) ? $_GET['tab'] : 'redirects';
		}

		protected function get_tabs() {
			return [
				'redirects' => Redirects::__( 'Redirects' ),
				'import'    => Redirects::__( 'Import'    ),
				'export'    => Redirects::__( 'Export'    ),
				'reset'     => Redirects::__( 'Reset'     )
			];
		}

		protected function get_action() {
			if ( ! empty( $_REQUEST['s'] ) ) return 'search';

			foreach( [ 'save', 'remove', 'parse', 'clear' ] as $action )
				if ( isset( $_REQUEST['action'] ) and $_REQUEST['action'] == $action )
					if ( isset( $_REQUEST['_wpnonce'] ) and wp_verify_nonce( $_REQUEST['_wpnonce'], $action ) ) return $action;
					else wp_die( Redirects::__( 'Cheatin&#8217; uh?' ) );

			return false;
		}

		protected function get_id() {
			return ( isset( $_GET['id'] )    and
				   is_numeric( $_GET['id'] ) and
				   isset( $this->redirects[(int)$_GET['id']] ) ) ? (int)$_GET['id'] : false;
		}

		protected function get_search() {
			return ! empty( $_REQUEST['s'] ) ? sanitize_text_field( (string)$_REQUEST['s'] ) : false;
		}

        static public function sanitize_url( $url ) {
            $url = sanitize_text_field( (string)$url );
            if ( '/' !== $url ) rtrim( $url, '/' );
            if ( ! empty( $url ) and ! preg_match( '/^https?:\/\//i', $url ) ) $url = ( '/' . ltrim( $url, '/' ) );
            return $url;
        }

        static public function sanitize_comparison( $comparison ) {
            return 'query' === $comparison ? 'query' : 'path';
        }

        static public function sanitize_parameters( $parameters ) {
            return (bool)$parameters;
        }

        static public function sanitize_code( $code ) {
           return 302 === (int)$code ? 302 : 301;
        }

		protected function save() {
			if ( empty( $_POST[Redirects::get( 'slug' )] ) or
			     ! is_array( $_POST[Redirects::get( 'slug' )] ) ) {
				self::notice( Redirects::__( 'Error while saving redirects occurred.' ) );
				return;
			}

			foreach( $_POST[Redirects::get( 'slug' )] as $id => $redirect ) {
				$id = (int)$id;

                foreach ( [ 'from', 'to' ] as $url )
                    $redirect[$url] = self::sanitize_url( $redirect[$url] );

				if ( empty( $redirect['from'] ) or empty( $redirect['to'] ) ) continue;
				$this->redirects[$id] = [
					'from'       => $redirect['from'],
					'to'         => $redirect['to'],
                    'comparison' => self::sanitize_comparison( $redirect['comparison'] ?? 'path' ),
                    'parameters' => self::sanitize_parameters( $redirect['parameters'] ?? true ),
					'code'       => self::sanitize_code( $redirect['code'] ?? 301 )
				];
			}

			$this->redirects = array_values( $this->redirects );
			if ( update_option( Redirects::get( 'slug' ), $this->redirects ) )
				self::notice( Redirects::__(  'Redirects saved successfully.' ), 'updated' );
			else self::notice( Redirects::__( 'Error while saving redirects occurred.' ) );
		}

		protected function remove() {
			if ( false === ( $id = $this->get_id() ) or
			     false === isset( $this->redirects[$id] ) ) {
				self::notice( Redirects::__( 'Error while removing redirect occurred.' ) );
				return;
			}

			unset( $this->redirects[$id] );
			$this->redirects = array_values( $this->redirects );
			if ( update_option( Redirects::get( 'slug' ), $this->redirects ) ) {
				self::notice( Redirects::__( 'Redirect removed successfully.' ), 'updated' );
				self::redirect();
			} else self::notice( Redirects::__( 'Error while removing redirect occurred.' ) );
		}

		protected function search() {
			if ( ! empty( $_POST['s'] ) and wp_safe_redirect( add_query_arg( [
				's' => $this->get_search()
			], admin_url( sprintf( self::URL, Redirects::get( 'slug' ) ) ) ) ) ) exit;
			if ( ! empty( $_GET['s'] ) ) $search = $this->get_search();

			$redirects = [];
			foreach( $this->redirects as $id => $redirect )
				foreach( [ 'from', 'to' ] as $field )
					if ( false !== strpos( $redirect[$field], $search ) ) {
						$redirects[$id] = $redirect;
						continue;
					}

			$this->redirects = $redirects;
		}

		protected function parse() {
			if ( empty( $_POST['import'] ) ) {
				self::notice( Redirects::__( 'Error while importing redirects occurred.' ) );
				return;
			}

			$import = sanitize_textarea_field( $_POST['import'] );
			$import = str_replace( '\"', '"', $import );
			$import = preg_split( "/\r\n|\n|\r/", $import );

			$redirects = [];
			foreach ( $import as $redirect ) {
				$redirect = str_getcsv( $redirect );
				if ( ! is_array( $redirect ) or 2 > count( $redirect ) ) continue;

				$redirects[] = [
					'from'       => rtrim( $redirect[0], '/' ),
					'to'         => rtrim( $redirect[1], '/' ),
                    'comparison' => self::sanitize_comparison( $redirect[2] ?? 'path' ),
					'parameters' => self::sanitize_parameters( $redirect[3] ?? true ),
					'code'       => self::sanitize_code( $redirect[4] ?? 301 )
				];
			}

			$this->redirects = array_merge( $this->redirects, $redirects );
			if ( update_option( Redirects::get( 'slug' ), $this->redirects ) )
				self::notice( Redirects::__( 'Redirects imported successfully.' ), 'updated' );
			else self::notice( Redirects::__( 'Error while importing redirects occurred.' ) );
		}

		protected function clear() {
			if ( delete_option( Redirects::get( 'slug' ) ) ) {
				$this->redirects = [];
				self::notice( Redirects::__( 'Redirects removed successfully.' ), 'updated' );
				self::redirect();
			} else self::notice( Redirects::__( 'Error while removing redirects occurred.' ) );
		}

		static public function notice( $message, $type = 'error' ) {
			add_settings_error(
				Redirects::get( 'slug' ),
				'settings_updated',
				$message,
				'error' == $type ? 'error' : 'updated'
			);
		}

		static public function redirect() {
			set_transient( 'settings_errors', get_settings_errors(), 30 );
			if ( wp_safe_redirect( add_query_arg( [
				'settings-updated' => 'true'
			], admin_url( sprintf( self::URL, Redirects::get( 'slug' ) ) ) ) ) ) exit;
		}

		static public function select( $args ) {
			$args = wp_parse_args( $args, [
				'selected' => null,
				'desc'     => ''
			] );
			extract( $args, EXTR_SKIP );

			return Redirects::render( 'select', [
				'name'     => isset( $name     ) ? $name     : '',
				'options'  => isset( $options  ) ? $options  : '',
				'selected' => isset( $selected ) ? $selected : '',
				'desc'     => isset( $desc     ) ? $desc     : '',
			] );
		}

		static public function input( $args ) {
			extract( $args, EXTR_SKIP );

			return Redirects::render( 'input', [
					'atts' => self::implode( [
						'type'  => isset( $type  ) ? $type  : '',
						'class' => isset( $class ) ? $class : '',
						'name'  => isset( $name  ) ? $name  : '',
						'value' => isset( $value ) ? $value : ''
					] ),
					'checked' => isset( $checked ) ? $checked : '',
					'before'  => isset( $before  ) ? $before  : '',
					'after'   => isset( $after   ) ? $after   : '',
					'desc'    => isset( $desc    ) ? $desc    : ''
				]
			);
		}

		static public function implode( $atts = [] ) {
			array_walk( $atts, function ( &$value, $key ) {
				$value = sprintf( '%s="%s"', $key, esc_attr( $value ) );
			} );

			return implode( ' ', $atts );
		}
	}
}
