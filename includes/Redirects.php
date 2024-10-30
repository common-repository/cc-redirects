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

namespace Clearcode;

use Clearcode\Redirects\Vendor\Clearcode\Framework\v3\Plugin;
use Clearcode\Redirects\Settings;

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( __NAMESPACE__ . '\Redirects' ) ) {
	class Redirects extends Plugin {
		public function activation()   {}
		public function deactivation() {}

		static public function request() {
			$request = set_url_scheme( '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
			return rtrim( str_ireplace( home_url(), '', $request ), '/' );
		}

		static public function redirect( $from, $to, $code ) {
			if ( ( '' !== $to ) && ( trim( $to, '/' ) !== trim( $from, '/' ) ) && ( 'wp-login.php' !== $GLOBALS['pagenow'] ) ) {
				if ( 0 === strpos( $to, '/' ) ) wp_safe_redirect( home_url() . $to, $code );
				else wp_redirect( $to, $code );
				exit;
			} return false;
		}

		public function action_plugins_loaded() {
            $redirects = Settings::instance()->redirects;

            if ( empty( $redirects ) ) return;
            if ( is_admin()          ) return;

			$request = self::request();

			if ( $redirect = $this->search( $request ) )
				return self::redirect( $request, $redirect['to'], $redirect['code'] );
		}

		public function search( $request ) {
			$redirects = Settings::instance()->redirects;
			if ( empty( $redirects ) ) return false;

            parse_str( parse_url( $request, PHP_URL_QUERY ), $parameters );

			foreach ( $redirects as $id => $redirect ) {
			    $redirect['id'] = $id;

                if ( (bool)($redirect['parameters'] ?? true ) )
                    $redirect['to'] = add_query_arg( $parameters, $redirect['to'] );

                $from = ( $redirect['comparison'] ?? 'path' ) === 'path' ? strtok( $request, '?' ) : $request;

				if ( false !== strpos( $redirect['from'], '*' ) ) {
					$redirect['from'] = str_replace( '*', '(.*)', $redirect['from'] );
					$pattern          = '/^' . str_replace( '/', '\/', $redirect['from'] ) . '/';
					$redirect['to']   = str_replace( '*', '$1', $redirect['to'] );
					$redirect['to']   = preg_replace( $pattern, $redirect['to'], $from );

					if ( $redirect['to'] !== $from ) return $redirect;

				} elseif ( rtrim( urldecode( $from ), '/' ) === rtrim( $redirect['from'], '/' ) ) return $redirect;
            }

            return false;
		}

		public function filter_plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
			if ( empty( $this->name          ) ) return $actions;
			if ( empty( $plugin_data['Name'] ) ) return $actions;
			if ( $this->name == $plugin_data['Name'] )
				array_unshift( $actions, self::render( 'link', [
					'url'  => get_admin_url( null, sprintf( Settings::URL, self::get( 'slug' ) ) ),
					'link' => self::__( 'Settings' ),
				] ) );

			return $actions;
		}

		public function filter_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
			if ( empty( self::get( 'name' )   ) ) return $plugin_meta;
			if ( empty( $plugin_data['Name']  ) ) return $plugin_meta;
			if ( self::get( 'name' ) == $plugin_data['Name'] ) {
				$plugin_meta[] = self::__( 'Author' ) . ' ' . self::render( 'link', [
						'url'  => 'http://piotr.press/',
						'link' => 'PiotrPress'
					] );
			}

			return $plugin_meta;
		}

		public function action_admin_notices() {
            global $pagenow;

            if ( 'post.php' !== $pagenow ) return;
            if ( ! isset( $_GET['post'] ) ) return;

            $request = str_replace( home_url(), '', get_permalink( $_GET['post'] ) );
            $request = rtrim( $request, '/' );

            if ( ! $redirect = $this->search( $request ) ) return;

            echo self::render( 'notice', [
                'class'   => 'warning',
                'content' => sprintf(
                    self::__( 'This post is redirected from: %s to: %s with code: %s.' ),
                    self::render( 'code', [ 'content' => $redirect['from'] ] ),
                    self::render( 'code', [ 'content' => $redirect['to'] ] ),
                    self::render( 'code', [ 'content' => $redirect['code'] ] )
                    ) . ' ' .
                    self::render( 'link', [
                        'link' => self::__( 'Edit' ),
                        'url'  => add_query_arg( 'id', $redirect['id'], sprintf( Settings::URL, Redirects::get( 'slug' ) ) )
            ] ) ] );
        }

        public function action_admin_init() {
            foreach ( get_post_types( [ 'public' => true ] ) as $post_type ) {
                add_filter( "manage_{$post_type}_posts_columns", [ $this, 'manage_posts_columns' ], 999, 2 );
                add_action( "manage_{$post_type}_posts_custom_column", [ $this, 'manage_posts_custom_column' ], 10, 2 );
            }
        }

		public function manage_posts_columns( $columns ) {
			return array_merge( $columns, [
				'redirect' => self::render( 'icon', [
					'icon' => 'migrate'
				] )
			] );
		}

		public function manage_posts_custom_column( $column_name, $post_id ) {
			if ( 'redirect' === $column_name ) {
				$url = str_replace( home_url(), '', get_permalink( $post_id ) );
				$url = rtrim( $url, '/' );

				if ( $redirect = $this->search( $url ) ) {
					printf( self::render( 'link', [
						'url' => add_query_arg( 'id', $redirect['id'], sprintf( Settings::URL, Redirects::get( 'slug' ) ) ),
						'link' => self::render( 'code', [
							'content' => $redirect['code']
						] )
					] ) );
				}
			}
		}
	}
}
