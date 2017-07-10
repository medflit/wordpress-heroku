<?php
/**
 * Portfolio Post Type
 *
 * @package   Filetrip_Post_Type
 * @author    Abdulrhman Elbuni
 * @license   GPL-2.0+
 * @copyright 2013-2014
 */

/**
 * Register arfaly types and taxonomies.
 *
 * @package Filetrip_Post_Type
 */
class Filetrip_Post_Type_Registrations {

	public $post_type = 'filetrip';
    public $c_post_type = 'Filetrip';

	public $taxonomies = array( 'filetrip_category', 'filetrip_tag' );

	public function init() {
		// Add the portfolio post type and taxonomies
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Initiate registrations of post type and taxonomies.
	 *
	 * @uses Portfolio_Post_Type_Registrations::register_post_type()
	 * @uses Portfolio_Post_Type_Registrations::register_taxonomy_tag()
	 * @uses Portfolio_Post_Type_Registrations::register_taxonomy_category()
	 */
	public function register() {
		$this->register_post_type();
		//$this->register_taxonomy_category();
		//$this->register_taxonomy_tag();
	}

	/**
	 * Register the custom post type.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type
	 */
	protected function register_post_type() {
		$labels = array(
			'name'               => __( $this->c_post_type, 'filetrip-plugin' ),
			'singular_name'      => __( $this->c_post_type.' Item', 'filetrip-plugin' ),
			'add_new'            => __( 'Add New Uploader', 'filetrip-plugin' ),
			'add_new_item'       => __( 'Add New '.$this->c_post_type.' Item', 'filetrip-plugin' ),
			'edit_item'          => __( 'Edit '.$this->c_post_type.' Item', 'filetrip-plugin' ),
			'new_item'           => __( 'Add New '.$this->c_post_type.' Item', 'filetrip-plugin' ),
			'view_item'          => __( 'View Item', 'filetrip-plugin' ),
			'search_items'       => __( 'Search '.$this->c_post_type.'', 'filetrip-plugin' ),
			'not_found'          => __( 'No '.$this->c_post_type.' items found', 'filetrip-plugin' ),
			'not_found_in_trash' => __( 'No '.$this->c_post_type.' items found in trash', 'filetrip-plugin' ),
		);

		$supports = array(
			'title',
//	'editor',
//	'excerpt',
//			'thumbnail',
//			'comments',
			'author',
//		'custom-fields',
//			'revisions',
		);

		$args = array(
			'labels'          => $labels,
			'supports'        => $supports,
			'public'          => true,
			'capability_type' => 'post',
			'rewrite'         => array( 'slug' => 'arfaly', ), // Permalinks format
			'menu_position'   => 5,
			'menu_icon'       => ( version_compare( $GLOBALS['wp_version'], '3.8', '>=' ) ) ? 'dashicons-cloud' : '',
			'has_archive'     => true,
		);

		$args = apply_filters( 'arfalyposttype_args', $args );

		register_post_type( $this->post_type, $args );
	}

	/**
	 * Register a taxonomy for '.$this->c_post_type.' Categories.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/register_taxonomy
	 */
	protected function register_taxonomy_category() {
		$labels = array(
			'name'                       => __( $this->c_post_type.' Categories', 'filetrip-plugin' ),
			'singular_name'              => __( $this->c_post_type.' Category', 'filetrip-plugin' ),
			'menu_name'                  => __( $this->c_post_type.' Categories', 'filetrip-plugin' ),
			'edit_item'                  => __( 'Edit'.$this->c_post_type.'Category', 'filetrip-plugin' ),
			'update_item'                => __( 'Update'.$this->c_post_type.'Category', 'filetrip-plugin' ),
			'add_new_item'               => __( 'Add New'.$this->c_post_type.'Category', 'filetrip-plugin' ),
			'new_item_name'              => __( 'New'.$this->c_post_type.'Category Name', 'filetrip-plugin' ),
			'parent_item'                => __( 'Parent'.$this->c_post_type.'Category', 'filetrip-plugin' ),
			'parent_item_colon'          => __( 'Parent'.$this->c_post_type.'Category:', 'filetrip-plugin' ),
			'all_items'                  => __( 'All'.$this->c_post_type.'Categories', 'filetrip-plugin' ),
			'search_items'               => __( 'Search'.$this->c_post_type.'Categories', 'filetrip-plugin' ),
			'popular_items'              => __( 'Popular'.$this->c_post_type.'Categories', 'filetrip-plugin' ),
			'separate_items_with_commas' => __( 'Separate arfaly categories with commas', 'filetrip-plugin' ),
			'add_or_remove_items'        => __( 'Add or remove arfaly categories', 'filetrip-plugin' ),
			'choose_from_most_used'      => __( 'Choose from the most used arfaly categories', 'filetrip-plugin' ),
			'not_found'                  => __( 'No arfaly categories found.', 'filetrip-plugin' ),
		);

		$args = array(
			'labels'            => $labels,
			'public'            => true,
			'show_in_nav_menus' => true,
			'show_ui'           => true,
			'show_tagcloud'     => true,
			'hierarchical'      => true,
			'rewrite'           => array( 'slug' => 'filetrip_category' ),
			'show_admin_column' => true,
			'query_var'         => true,
		);

		$args = apply_filters( 'arfalyposttype_category_args', $args );

		register_taxonomy( $this->taxonomies[0], $this->post_type, $args );
	}

	/**
	 * Register a taxonomy for'.$this->c_post_type.'Tags.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/register_taxonomy
	 */
	protected function register_taxonomy_tag() {
		$labels = array(
			'name'                       => __( $this->c_post_type.' Tags', 'filetrip-plugin' ),
			'singular_name'              => __( $this->c_post_type.' Tag', 'filetrip-plugin' ),
			'menu_name'                  => __( $this->c_post_type.' Tags', 'filetrip-plugin' ),
			'edit_item'                  => __( 'Edit'.$this->c_post_type.'Tag', 'filetrip-plugin' ),
			'update_item'                => __( 'Update'.$this->c_post_type.'Tag', 'filetrip-plugin' ),
			'add_new_item'               => __( 'Add New'.$this->c_post_type.'Tag', 'filetrip-plugin' ),
			'new_item_name'              => __( 'New'.$this->c_post_type.'Tag Name', 'filetrip-plugin' ),
			'parent_item'                => __( 'Parent'.$this->c_post_type.'Tag', 'filetrip-plugin' ),
			'parent_item_colon'          => __( 'Parent'.$this->c_post_type.'Tag:', 'filetrip-plugin' ),
			'all_items'                  => __( 'All'.$this->c_post_type.'Tags', 'filetrip-plugin' ),
			'search_items'               => __( 'Search'.$this->c_post_type.'Tags', 'filetrip-plugin' ),
			'popular_items'              => __( 'Popular'.$this->c_post_type.'Tags', 'filetrip-plugin' ),
			'separate_items_with_commas' => __( 'Separate arfaly tags with commas', 'filetrip-plugin' ),
			'add_or_remove_items'        => __( 'Add or remove arfaly tags', 'filetrip-plugin' ),
			'choose_from_most_used'      => __( 'Choose from the most used arfaly tags', 'filetrip-plugin' ),
			'not_found'                  => __( 'No arfaly tags found.', 'filetrip-plugin' ),
		);

		$args = array(
			'labels'            => $labels,
			'public'            => true,
			'show_in_nav_menus' => true,
			'show_ui'           => true,
			'show_tagcloud'     => true,
			'hierarchical'      => false,
			'rewrite'           => array( 'slug' => 'filetrip_tag' ),
			'show_admin_column' => true,
			'query_var'         => true,
		);

		$args = apply_filters( 'arfalyposttype_tag_args', $args );

		register_taxonomy( $this->taxonomies[1], $this->post_type, $args );

	}
}
