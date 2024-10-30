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

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( __NAMESPACE__ . '\Plugin' ) ) {
    abstract class Plugin {
        use Singleton;

        protected $name        = '';
        protected $plugin_uri  = '';
        protected $version     = '';
        protected $description = '';
        protected $author      = '';
        protected $author_uri  = '';
        protected $text_domain = '';
        protected $domain_path = '';
        protected $network     = '';
        protected $title       = '';
        protected $author_name = '';
        protected $file        = '';
        protected $basename    = '';
        protected $dir         = '';
        protected $url         = '';
        protected $slug        = '';

        protected function set_plugin_data( $file ) {
            if ( ! function_exists( 'get_plugin_data' ) )
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            $data = get_plugin_data( $file );

            $data['plugin_uri']  = $data['PluginURI'];
            $data['author_uri']  = $data['AuthorURI'];
            $data['text_domain'] = $data['TextDomain'];
            $data['domain_path'] = $data['DomainPath'];
            $data['author_name'] = $data['AuthorName'];

            foreach( [
                'PluginURI',
                'AuthorURI',
                'TextDomain',
                'DomainPath',
                'AuthorName' ] as $key ) {
                unset( $data[$key] );
            }

            $data['file']     = $file;
            $data['basename'] = plugin_basename(   $file   );
            $data['dir']      = plugin_dir_path(   $file   );
            $data['url']      = plugin_dir_url(    $file   );
            $data['slug']     = basename( dirname( $file ) );

            foreach( $data as $key => $value ) {
                $key = strtolower( $key );
                if ( isset( $this->$key ) )
                    $this->$key = $value;
            }
        }

        protected function __construct( $file ) {
            $this->set_plugin_data( $file );

            new Filterer( $this );

            register_activation_hook(   $this->file, [ $this, 'activation'   ] );
            register_deactivation_hook( $this->file, [ $this, 'deactivation' ] );
        }

        abstract public function activation();

        abstract public function deactivation();

        public function action_activated_plugin( $plugin, $network_wide = null ) {
            $this->switch_plugin_hook( $plugin, $network_wide );
        }

        public function action_deactivated_plugin( $plugin, $network_wide = null ) {
            $this->switch_plugin_hook( $plugin, $network_wide );
        }

        public function filter_network_admin_plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
            return $actions;
        }

        public function filter_plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
            return $actions;
        }

        protected function switch_plugin_hook( $plugin, $network_wide = null ) {
            if ( static::get( 'basename' ) != $plugin ) return;
            if ( ! $network_wide )                      return;

            list( $hook ) = explode( '_', current_filter(), 2 );
            $hook = str_replace( 'activated', 'activate_', $hook );
            $hook .= plugin_basename( static::get( 'file' ) );

            $this->call_user_func_array( 'do_action', [ $hook, false ] );
        }

        protected function get_sites( $args = [] ) {
            $args  = wp_parse_args( $args, [ 'public' => 1 ] );
            $sites = function_exists( 'get_sites' ) ? get_sites( $args ) : wp_get_sites( $args );

            return $sites ? array_map( function( $site ) { return (array)$site; }, $sites ) : false;
        }

        protected function call_user_func_array( $function, $args = [] ) {
            if ( is_multisite() ) {
                $sites = $this->get_sites();

                foreach ( $sites as $site ) {
                    switch_to_blog( $site['blog_id'] );
                    call_user_func_array( $function, $args );
                }

                restore_current_blog();
            } else $function( $args );
        }

        public function action_init_0(){
            load_plugin_textdomain( static::get( 'text_domain' ), false, static::get( 'slug' ) . static::get( 'domain_path' ) );
        }

        static public function get( $name ) {
            $class = static::class;
            return $class::instance()->$name;
        }

        static public function __( $text ) {
            return __( $text, static::get( 'text_domain' ) );
        }

        static public function render( $template, $vars = [] ) {
            $template = static::get( 'dir' ) . 'templates/' . $template . '.php';
            $template = static::apply_filters( 'template', $template, $vars );
            if ( ! is_file( $template ) ) return false;

            $vars = static::apply_filters( 'vars', $vars, $template );
            if ( is_array( $vars ) ) extract( $vars, EXTR_SKIP );

            ob_start();
            include $template;

            return ob_get_clean();
        }

        static public function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
            return add_filter( static::get( 'slug' )  . '\\' . $tag, $function_to_add, $priority, $accepted_args );
        }

        static public function apply_filters( $tag, $value ) {
            $args    = func_get_args();
            $args[0] = static::get( 'slug' ) . '\\' . $args[0];

            return call_user_func_array( 'apply_filters', $args );
        }
    }
}
