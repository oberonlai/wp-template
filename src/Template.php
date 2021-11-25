<?php

namespace ODS;

/**
 * A simple class that allows registering Page templates from plugins.
 * Modified version of http://www.wpexplorer.com/wordpress-page-templates-plugin/
 *
 * Class Template
 */
class Template {

	/* @var array */
	protected $templates = array();
	private $path;

	/**
	 * PageTemplateManager constructor.
	 */
	public function __construct( $path ) {
		$this->path = $path;

		 // Add our custom templates to page template dropdown in WP Admin
		add_filter( 'theme_page_templates', array( $this, 'registerTemplate' ) );

		// On saving a post, inject our templates into the page cache
		add_filter( 'wp_insert_post_data', array( $this, 'cacheTemplate' ) );

		// Render our custom template, if applicable
		add_filter( 'template_include', array( $this, 'renderTemplate' ) );

	}

	/**
	 * Add a new custom page template.
	 *
	 * @param $file string Full path to the template file
	 * @param $name string Human-readable template name
	 */
	public function add_page( $file, $name ) {
		$this->templates[ $this->path . $file ] = $name;
	}

	/**
	 * Add a new custom page slug template.
	 *
	 * @param $file string Full path to the template file
	 * @param $slug string for matched page slug
	 */
	public function add_page_slug( $file, $slug ) {
		add_action(
			'page_template',
			function() use ( $file, $slug ) {
				if ( is_page( $slug ) ) {
					return $this->path . $file;
				}
			},
			99,
			1,
		);
	}

	/**
	 * Add a new custom page slug template.
	 *
	 * @param $file string Full path to the template file
	 * @param $type string for custom post type
	 * @param $position string for template type eg.single, archive
	 */
	public function add_post( $file, $type, $position ) {
		add_action(
			$position . '_template',
			function() use ( $file, $type, $position ) {
				if ( 'single' === $position && is_singular( $type ) ) {
					return $this->path . $file;
				} elseif ( 'archive' === $position && is_post_type_archive( $type ) ) {
					return $this->path . $file;
				}
			},
			99,
			1,
		);
	}

	/**
	 * TODO:
	 * Add a new custom page slug template.
	 *
	 * @param $file string Full path to the template file
	 * @param $taxonomy string for custom taxonomy
	 * @param $position string for template type eg. taxonomy, term
	 */
	public function add_term( $file, $taxonomy, $position ) {
		add_action(
			'taxonomy_template',
			function() use ( $file, $taxonomy, $position ) {
				if ( is_tax( $taxonomy ) ) {
					return $this->path . $file;
				}
			},
			99,
			1,
		);
	}

	public function add_wp( $file, $position ) {
		add_action(
			$position . '_template',
			function() use ( $file ) {
				return $this->path . $file;
			},
			99,
			1,
		);
	}


	/**
	 * Assign the path of WooCommerce template path.
	 */
	public function add_woocommerce() {
		add_filter( 'wc_get_template', array( $this, 'intercept_wc_template' ), 99, 3 );
	}

	public function intercept_wc_template( $template, $template_name, $template_path ) {
		$path = $this->path . 'woocommerce/' . $template_name;
		return file_exists( $path ) ? $path : $template;
	}

	/**
	 * Add our custom templates to page template dropdown in WP Admin
	 *
	 * @param $templates
	 * @return array
	 */
	public function registerTemplate( $templates ) {
		return array_merge( $templates, $this->templates );
	}

	/**
	 * Add our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists
	 *
	 * @param $data
	 * @return mixed
	 */
	public function cacheTemplate( $data ) {
		// Create the key used for the themes cache
		$cacheKey = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list. If it doesn't exist or if it's empty, set up a new one
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		// Remove the old cache
		wp_cache_delete( $cacheKey, 'themes' );

		// Add our custom templates to the list of WP's own templates
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing available templates
		wp_cache_add( $cacheKey, $templates, 'themes', 1800 );

		return $data;
	}

	/**
	 * Check if one of the registered templates is assigned to the current page.
	 * If it is, then render it.
	 *
	 * @param $template
	 * @return string
	 */
	public function renderTemplate( $template ) {
		// If we're searching, bail.
		if ( is_search() ) {
			return $template;
		}

		// If we're viewing something that's not a post, bail.
		global $post;
		if ( ! $post ) {
			return $template;
		}

		// If the page doesn't have one of our custom templates assigned, bail.
		$currentTemplate = get_post_meta( $post->ID, '_wp_page_template', true );
		if ( ! isset( $this->templates[ $currentTemplate ] ) ) {
			return $template;
		}

		// Now we've made sure that this is one of our custom templates.
		// If the template file actually exists, include it.
		if ( file_exists( $currentTemplate ) ) {
			return $currentTemplate;
		}

		// Otherwise, trigger an error and return the default template.
		trigger_error( "Template file {$currentTemplate} does not exist.", E_USER_WARNING );

		return $template;
	}
}
