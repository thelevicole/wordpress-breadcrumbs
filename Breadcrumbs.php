<?php

/**
 * Build and render a <ul> list of parents and current page
 *
 * An alias function `get_breadcrumbs()` is available and
 * implimented after the Breadcrumbs class closes
 * 
 * @author Levi Cole <hello@thelevicole.com>
 *
 */
class Breadcrumbs {

	private $args;

	function __construct( $options = [] ) {

		$this->args = wp_parse_args( $options, [
			'separator'		=> '/',
			'id'			=> '',
			'class'			=> 'breadcrumbs',
			'include_front'	=> true,
			'front_title'	=> $this->front_title( 'Home' ),
			'taxonomies'	=> get_taxonomies( [ 'public' => true, '_builtin' => false ] )
		] );

	}

	/**
	 * Renderer
	 * 
	 * @param	boolean	$echo	Determin whether to return the element or echo
	 */
	function render( $echo = true ) {

		// Render only if not the front page
		if ( !is_front_page() ) {

			// Open the list
			$breadcrumbs = '<ul id="' . $this->args[ 'id' ] . '" class="' . $this->args[ 'class' ] . '">';

				$breadcrumbs .= $this->get_front();
				$breadcrumbs .= $this->get_archive();
				$breadcrumbs .= $this->get_taxonomy();
				$breadcrumbs .= $this->get_single();
				$breadcrumbs .= $this->get_category();
				$breadcrumbs .= $this->get_page();
				$breadcrumbs .= $this->get_tag();
				$breadcrumbs .= $this->get_day();
				$breadcrumbs .= $this->get_month();
				$breadcrumbs .= $this->get_year();
				$breadcrumbs .= $this->get_author();
				$breadcrumbs .= $this->get_paged();
				$breadcrumbs .= $this->get_search();
				$breadcrumbs .= $this->get_404();

			// Close the list
			$breadcrumbs .= '</ul>';

			// Send to user
			if ( $echo ) {
				echo $breadcrumbs;
			} else {
				return $breadcrumbs;
			}

		}

	}

	/**
	 * Inline array of classes into a single line ready for printing
	 *
	 * @param	array	$classes
	 * @return	string
	 */
	function class_inliner( array $classes ) {
		return trim( preg_replace( '/\s+/', ' ', implode( array_map('sanitize_html_class', $classes), ' ' ) ) );
	}

	/**
	 * Get the separator object with optional class
	 *
	 * @param	string	$class
	 * @return	string
	 */
	function separator( $class = null ) {
		if ( !empty( $this->args[ 'separator' ] ) ) {
			$classes = [
				'separator',
				$class ? ' separator-' . $class : ''
			];
			ob_start(); ?>
				<li class="<?php echo $this->class_inliner( $classes ); ?>"><?php echo $this->args[ 'separator' ]; ?></li>
			<?php return ob_get_clean();
		}
	}

	/**
	 * Create the current page list item
	 *
	 * @param	string	$title	The title of the page
	 * @param	string	$slug	The slug of the page, title used if empty
	 * @return	string
	 */
	function current($title, $slug = null) {
		$slug = sanitize_html_class( $slug ?: sanitize_title( $title ) );
		ob_start(); ?>
			<li class="<?php echo $this->class_inliner( [ 'item-current', 'item-' . $slug ] ); ?>">
				<strong class="<?php echo $this->class_inliner( [ 'bread-current', 'bread-' . $slug ] ); ?>" title="<?php echo $title; ?>"><?php echo $title; ?></strong>
			</li>
		<?php return ob_get_clean();
	}

	/**
	 * Create a parent page list item
	 *
	 * @param	string	$title	The title of the page
	 * @param	string	$slug	The slug of the page, title used if empty
	 * @return	string
	 */
	function parent($title, $link, $slug = null) {
		$slug = $slug ?: sanitize_title( $title );
		ob_start(); ?>
			<li class="<?php echo $this->class_inliner( [ 'item-link', 'item-' . $slug ] ); ?>">
				<a class="<?php echo $this->class_inliner( [ 'bread-link', 'bread-' . $slug ] ); ?>" href="<?php echo $link; ?>" title="<?php echo $title; ?>">
					<?php echo $title; ?>
				</a>
			</li>
		<?php return ob_get_clean();
	}

	/**
	 * Get the current page
	 *
	 * @return	string
	 */
	function this_page() {
		return $this->current( get_the_title(), get_the_ID() );
	}

	/**
	 * Get the front page title from the home url
	 *
	 * @param	string	$fallback	If front is not a page, return this
	 * @return	string
	 */
	function front_title( $fallback ) {
		if ( $front_id = url_to_postid( get_home_url() ) ) {
			return get_the_title( $front_id );
		}

		return $fallback;
	}


	/* -------------------------------------------------------- */

	/**
	 * Custom post type renderer
	 */
	function get_custom_post_type() {
		$post_type = get_post_type();

		// If it is a custom post type display name and link
		if ( $post_type !== 'post' ) {
			$post_type_object	= get_post_type_object( $post_type );
			$post_type_archive	= get_post_type_archive_link( $post_type );

			ob_start(); ?>
				<?php echo $this->parent( $post_type_object->labels->name, $post_type_archive, 'custom-post-type' . $post_type ); ?>
				<?php echo $this->separator(); ?>
			<?php return ob_get_clean();
		}
	}

	/**
	 * Front page renderer
	 */
	function get_front() {
		if ( $this->args[ 'include_front' ] ) {
			ob_start(); ?>
				<?php echo $this->parent( $this->args[ 'front_title' ], get_home_url(), 'front' ); ?>
				<?php echo $this->separator( 'front' ); ?>
			<?php return ob_get_clean();
		}
	}

	/**
	 * Archive renderer
	 */
	function get_archive() {
		if ( is_archive() && !is_tax() && !is_category() && !is_tag() ) {
			return $this->current( post_type_archive_title(), 'archive' );
		}
	}

	/**
	 * Taxonomy renderer
	 */
	function get_taxonomy() {
		if ( is_archive() && is_tax() && !is_category() && !is_tag() ) {
			ob_start(); ?>
				<?php echo $this->get_custom_post_type(); ?>
				<?php echo $this->get_archive(); ?>
				<?php if ( $custom_tax = get_queried_object() ): ?>
					<?php echo $this->current( $custom_tax->name, 'archive' ); ?>
				<?php endif ?>
			<?php return ob_get_clean();
		}
	}

	/**
	 * Category renderer
	 */
	function get_category() {
		if ( is_category() ) {
			return $this->current( single_cat_title( '', false ), 'category' );
		}
	}

	/**
	 * Single renderer
	 */
	function get_single() {
		if ( is_single() ) {

			// Include taxonomies
			if ( !empty( $this->args[ 'taxonomies' ] ) ) {

				$taxonomy_crumbs = '';

				foreach ( $this->args[ 'taxonomies' ] as $taxonomy ) {

					if ( $terms = wp_get_post_terms( get_the_ID(), $taxonomy ) ) {
						$last_term		= end( $terms );
						$term_ancestors	= get_ancestors( $last_term->term_id, $taxonomy, 'taxonomy' );

						ob_start(); ?>
							<?php echo $this->get_custom_post_type(); ?>

							<?php foreach ( $term_ancestors as $ancestors ): ?>
								<?php if ( $term = get_term_by('id', $ancestors, $taxonomy) ): ?>
									<?php echo $this->parent( $term->name, get_term_link( $term ), $taxonomy ); ?>
									<?php echo $this->separator(); ?>
								<?php endif ?>
							<?php endforeach ?>

							<?php echo $this->parent( $last_term->name, get_term_link( $last_term ), $taxonomy ); ?>
							<?php echo $this->separator(); ?>

						<?php $taxonomy_crumbs .= ob_get_clean();

					}

				}

				return $taxonomy_crumbs . $this->this_page();
			}

			// End page
			else {
				return $this->this_page();
			}

		}
	}

	/**
	 * Page renderer
	 */
	function get_page() {
		if ( is_page() ) {

			/**
			 * If page has ancestors
			 *
			 * @see https://codex.wordpress.org/Function_Reference/wp_get_post_parent_id
			 */
			if ( wp_get_post_parent_id( get_the_ID() ) ) {

				// Get ancestors
				$ancestors = get_post_ancestors( get_the_ID() );

				// Get parents in the right order
				$ancestors = array_reverse( $ancestors );

				ob_start(); ?>
					<?php if ( $ancestors ): ?>
						<?php foreach ( $ancestors as $ancestor ): ?>
							<?php echo $this->parent( get_the_title( $ancestor ), get_permalink( $ancestor ), 'parent-page' ); ?>
							<?php echo $this->separator( $ancestor ); ?>
						<?php endforeach ?>
					<?php endif ?>

					<?php echo $this->this_page(); ?>
				<?php return ob_get_clean();


			}

			// If no ancestors, display it's self
			else {
				return $this->this_page();
			}

		}
	}

	/**
	 * Tag archive renderer
	 */
	function get_tag() {
		if ( is_tag() ) {

			// Get tag information
			$term_id	= get_query_var('tag_id');
			$terms		= get_terms( 'post_tag', [ 'include' => $term_id ] );

			if ( !empty( $terms ) ) {
				$get_term_slug	= $terms[0]->slug;
				$get_term_name	= $terms[0]->name;
				return $this->current( $get_term_name, $get_term_slug );
			}

		}
	}

	/**
	 * Day archive renderer
	 */
	function get_day() {
		if ( is_day() ) {
			ob_start(); ?>

				<?php echo $this->parent( get_the_time('Y') . ' Archives', get_year_link( get_the_time('Y') ), 'year-' . get_the_time('Y') ); ?>
				<?php echo $this->separator( get_the_time('Y') ); ?>
				<?php echo $this->parent( get_the_time('M') . ' Archives', get_month_link( get_the_time('Y'), get_the_time('m') ), 'month-' . get_the_time('m') ); ?>
				<?php echo $this->separator( get_the_time('m') ); ?>
				<?php echo $this->current( get_the_time('jS M') . ' Archives', get_the_time('j') ); ?>

			<?php return ob_get_clean();
		}
	}

	/**
	 * Month archive renderer
	 */
	function get_month() {
		if ( is_month() ) {
			ob_start(); ?>

				<?php echo $this->parent( get_the_time('Y') . ' Archives', get_year_link( get_the_time('Y') ), 'year-' . get_the_time('Y') ); ?>
				<?php echo $this->separator( get_the_time('Y') ); ?>
				<?php echo $this->current( get_the_time('M') . ' Archives', get_the_time('m') ); ?>

			<?php return ob_get_clean();
		}
	}

	/**
	 * Year archive renderer
	 */
	function get_year() {
		if ( is_year() ) {
			return $this->current( get_the_time('Y') . ' Archives', get_the_time('Y') );
		}
	}

	/**
	 * Author archive renderer
	 */
	function get_author() {
		if ( is_author() ) {

			// Get the author information
			global $author;

			if ( $author ) {
				$userdata = get_userdata( $author );

				return $this->current( 'Author: ' . $userdata->display_name, $userdata->user_nicename );

			}

		}
	}

	/**
	 * Paginated renderer
	 */
	function get_paged() {
		if ( $paged = get_query_var('paged') ) {

			return $this->current( 'Page ' . $paged, $paged );

		}
	}

	/**
	 * Search results renderer
	 */
	function get_search() {
		if ( is_search() ) {

			return $this->current( 'Search results for: ' . get_search_query(), 'search' );

		}
	}

	/**
	 * 404 renderer
	 */
	function get_404() {
		if ( is_404() ) {

			return $this->current( 'Error 404', 'error-404' );

		}
	}

}


/**
 * Render custom breadcrumbs for the current page
 * 
 * @param	array	$options
 * @param	boolean	$echo		Determin whether to print or echo the breadcrumbs
 * @return	void
 */
function get_breadcrumbs( $options = [], $echo = true ) {
	$breadcrumbs = new Breadcrumbs( $options );
	$breadcrumbs->render( $echo );
}




