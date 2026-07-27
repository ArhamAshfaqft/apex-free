<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Conditions_Engine {

	public static function get_condition_groups() {
		$groups = [
			'general' => [
				'label'    => esc_html__( 'General', 'apex-addons-for-elementor' ),
				'options'  => [
					'general'  => esc_html__( 'Entire Site', 'apex-addons-for-elementor' ),
					'singular' => esc_html__( 'Singular', 'apex-addons-for-elementor' ),
					'archive'  => esc_html__( 'Archives', 'apex-addons-for-elementor' ),
				],
				'sub_names' => [
					'singular' => 'singular_sub_names',
					'archive'  => 'archive_sub_names',
				],
			],
		];

		if ( class_exists( 'WooCommerce' ) ) {
			$groups['woocommerce'] = [
				'label'    => esc_html__( 'WooCommerce', 'apex-addons-for-elementor' ),
				'options'  => [
					'woocommerce' => esc_html__( 'WooCommerce', 'apex-addons-for-elementor' ),
				],
				'sub_names' => [
					'woocommerce' => 'woocommerce_sub_names',
				],
			];
		}

		return $groups;
	}

	public static function get_singular_sub_names() {
		return [
			''                  => esc_html__( 'All Singular', 'apex-addons-for-elementor' ),
			'front_page'        => esc_html__( 'Front Page', 'apex-addons-for-elementor' ),
			'post'              => esc_html__( 'Posts', 'apex-addons-for-elementor' ),
			'page'              => esc_html__( 'Pages', 'apex-addons-for-elementor' ),
			'attachment'        => esc_html__( 'Media', 'apex-addons-for-elementor' ),
			'child_of'          => esc_html__( 'Direct Child of', 'apex-addons-for-elementor' ),
			'any_child_of'      => esc_html__( 'Any Child of', 'apex-addons-for-elementor' ),
			'by_author'         => esc_html__( 'By Author', 'apex-addons-for-elementor' ),
			'not_found404'      => esc_html__( '404 Page', 'apex-addons-for-elementor' ),
		];
	}

	public static function get_archive_sub_names() {
		return [
			''                       => esc_html__( 'All Archives', 'apex-addons-for-elementor' ),
			'author'                 => esc_html__( 'Author Archive', 'apex-addons-for-elementor' ),
			'date'                   => esc_html__( 'Date Archive', 'apex-addons-for-elementor' ),
			'search'                 => esc_html__( 'Search Results', 'apex-addons-for-elementor' ),
			'post_archive'           => esc_html__( 'Posts Archive', 'apex-addons-for-elementor' ),
			'category'               => esc_html__( 'Categories', 'apex-addons-for-elementor' ),
			'child_of_category'      => esc_html__( 'Direct child Category of', 'apex-addons-for-elementor' ),
			'any_child_of_category'  => esc_html__( 'Any child Category of', 'apex-addons-for-elementor' ),
			'post_tag'               => esc_html__( 'Tags', 'apex-addons-for-elementor' ),
		];
	}

	public static function get_post_singular_sub_names() {
		return [
			'post'                    => esc_html__( 'Posts', 'apex-addons-for-elementor' ),
			'in_category'             => esc_html__( 'In Category', 'apex-addons-for-elementor' ),
			'in_category_children'    => esc_html__( 'In child Categories', 'apex-addons-for-elementor' ),
			'in_post_tag'              => esc_html__( 'In Tag', 'apex-addons-for-elementor' ),
			'post_by_author'          => esc_html__( 'Posts by author', 'apex-addons-for-elementor' ),
		];
	}

	public static function get_page_singular_sub_names() {
		return [
			'page'                  => esc_html__( 'Pages', 'apex-addons-for-elementor' ),
			'page_by_author'        => esc_html__( 'Pages by author', 'apex-addons-for-elementor' ),
		];
	}

	public static function get_woocommerce_sub_names() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return [];
		}

		return [
			''                            => esc_html__( 'All WooCommerce', 'apex-addons-for-elementor' ),
			'shop_page'                    => esc_html__( 'Shop Page', 'apex-addons-for-elementor' ),
			'product'                      => esc_html__( 'Product', 'apex-addons-for-elementor' ),
			'product_in_category'          => esc_html__( 'Product In Category', 'apex-addons-for-elementor' ),
			'product_in_tag'              => esc_html__( 'Product In Tag', 'apex-addons-for-elementor' ),
			'product_archive'             => esc_html__( 'Product Archive', 'apex-addons-for-elementor' ),
			'product_category'            => esc_html__( 'Product Categories', 'apex-addons-for-elementor' ),
			'product_tag'                 => esc_html__( 'Product Tags', 'apex-addons-for-elementor' ),
			'cart'                         => esc_html__( 'Cart Page', 'apex-addons-for-elementor' ),
			'checkout'                     => esc_html__( 'Checkout Page', 'apex-addons-for-elementor' ),
			'myaccount'                    => esc_html__( 'My Account', 'apex-addons-for-elementor' ),
		];
	}

	public static function get_sub_name_options_for( $parent_name ) {
		if ( 'general' === $parent_name || '' === $parent_name ) {
			return [ '' => esc_html__( 'Entire Site', 'apex-addons-for-elementor' ) ];
		}
		if ( 'singular' === $parent_name ) {
			return self::get_singular_sub_names();
		}
		if ( 'archive' === $parent_name ) {
			return self::get_archive_sub_names();
		}
		if ( 'woocommerce' === $parent_name ) {
			return self::get_woocommerce_sub_names();
		}
		return [];
	}

	public static function needs_sub_id( $sub_name ) {
		$needs_id = [
			'page', 'post', 'product', 'author',
			'child_of', 'any_child_of', 'by_author',
			'category', 'child_of_category', 'any_child_of_category', 'post_tag',
			'in_category', 'in_category_children', 'in_post_tag', 'post_by_author',
			'page_by_author',
			'product_in_category', 'product_in_tag',
			'product_category', 'product_tag',
		];
		return in_array( $sub_name, $needs_id, true );
	}

	public static function get_sub_id_label( $sub_name ) {
		$labels = [
			'page'                 => esc_html__( 'Select Page', 'apex-addons-for-elementor' ),
			'post'                 => esc_html__( 'Select Post', 'apex-addons-for-elementor' ),
			'product'              => esc_html__( 'Select Product', 'apex-addons-for-elementor' ),
			'author'               => esc_html__( 'Select Author', 'apex-addons-for-elementor' ),
			'child_of'             => esc_html__( 'Select Parent Page', 'apex-addons-for-elementor' ),
			'any_child_of'         => esc_html__( 'Select Parent Page', 'apex-addons-for-elementor' ),
			'by_author'            => esc_html__( 'Select Author', 'apex-addons-for-elementor' ),
			'category'             => esc_html__( 'Select Category', 'apex-addons-for-elementor' ),
			'child_of_category'    => esc_html__( 'Select Parent Category', 'apex-addons-for-elementor' ),
			'any_child_of_category'=> esc_html__( 'Select Parent Category', 'apex-addons-for-elementor' ),
			'post_tag'              => esc_html__( 'Select Tag', 'apex-addons-for-elementor' ),
			'in_category'          => esc_html__( 'Select Category', 'apex-addons-for-elementor' ),
			'in_category_children'=> esc_html__( 'Select Category', 'apex-addons-for-elementor' ),
			'in_post_tag'           => esc_html__( 'Select Tag', 'apex-addons-for-elementor' ),
			'post_by_author'       => esc_html__( 'Select Author', 'apex-addons-for-elementor' ),
			'page_by_author'         => esc_html__( 'Select Author', 'apex-addons-for-elementor' ),
			'product_in_category'  => esc_html__( 'Select Product Category', 'apex-addons-for-elementor' ),
			'product_in_tag'       => esc_html__( 'Select Product Tag', 'apex-addons-for-elementor' ),
			'product_category'     => esc_html__( 'Select Product Category', 'apex-addons-for-elementor' ),
			'product_tag'          => esc_html__( 'Select Product Tag', 'apex-addons-for-elementor' ),
		];
		return $labels[ $sub_name ] ?? esc_html__( 'Select Value', 'apex-addons-for-elementor' );
	}

	public static function get_sub_id_options( $sub_name ) {
		if ( in_array( $sub_name, [ 'author', 'by_author', 'post_by_author', 'page_by_author' ], true ) ) {
			$users = get_users( [ 'who' => 'authors', 'number' => 100 ] );
			$opts = [];
			foreach ( $users as $user ) {
				$opts[ $user->ID ] = $user->display_name;
			}
			return $opts;
		}

		if ( in_array( $sub_name, [ 'in_category', 'category', 'child_of_category', 'any_child_of_category', 'in_category_children' ], true ) ) {
			$terms = get_terms( [ 'taxonomy' => 'category', 'hide_empty' => false ] );
			$opts = [];
			foreach ( $terms as $term ) {
				$opts[ $term->term_id ] = $term->name;
			}
			return $opts;
		}

		if ( in_array( $sub_name, [ 'in_post_tag', 'post_tag' ], true ) ) {
			$terms = get_terms( [ 'taxonomy' => 'post_tag', 'hide_empty' => false ] );
			$opts = [];
			foreach ( $terms as $term ) {
				$opts[ $term->term_id ] = $term->name;
			}
			return $opts;
		}

		if ( in_array( $sub_name, [ 'page', 'child_of', 'any_child_of' ], true ) ) {
			$pages = get_posts( [ 'post_type' => 'page', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
			$opts = [];
			foreach ( $pages as $page ) {
				$opts[ $page->ID ] = $page->post_title;
			}
			return $opts;
		}

		if ( 'post' === $sub_name ) {
			$posts = get_posts( [ 'post_type' => 'post', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
			$opts = [];
			foreach ( $posts as $post ) {
				$opts[ $post->ID ] = $post->post_title;
			}
			return $opts;
		}

		if ( 'product' === $sub_name && class_exists( 'WooCommerce' ) ) {
			$products = get_posts( [ 'post_type' => 'product', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
			$opts = [];
			foreach ( $products as $product ) {
				$opts[ $product->ID ] = $product->post_title;
			}
			return $opts;
		}

		if ( in_array( $sub_name, [ 'product_in_category', 'product_category' ], true ) && class_exists( 'WooCommerce' ) ) {
			$terms = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => false ] );
			$opts = [];
			foreach ( $terms as $term ) {
				$opts[ $term->term_id ] = $term->name;
			}
			return $opts;
		}

		if ( in_array( $sub_name, [ 'product_in_tag', 'product_tag' ], true ) && class_exists( 'WooCommerce' ) ) {
			$terms = get_terms( [ 'taxonomy' => 'product_tag', 'hide_empty' => false ] );
			$opts = [];
			foreach ( $terms as $term ) {
				$opts[ $term->term_id ] = $term->name;
			}
			return $opts;
		}

		return [];
	}

	public static function is_template_match( $conditions, $template_type ) {
		if ( ! is_array( $conditions ) ) {
			$conditions = [ [ 'type' => 'include', 'name' => 'general', 'sub_name' => '', 'sub_id' => '' ] ];
		}

		if ( empty( $conditions ) ) {
			return false;
		}

		$has_include_conditions = false;
		$include_match = false;
		$exclude_match = false;

		foreach ( $conditions as $condition ) {
			$cond_type = $condition['type'] ?? 'include';
			$name      = $condition['name'] ?? 'general';
			$sub_name  = $condition['sub_name'] ?? '';
			$sub_id    = $condition['sub_id'] ?? '';

			if ( 'include' === $cond_type ) {
				$has_include_conditions = true;
			}

			$matched = self::check_single_condition( $name, $sub_name, $sub_id );

			if ( 'exclude' === $cond_type ) {
				if ( $matched ) {
					$exclude_match = true;
				}
			} else {
				if ( $matched ) {
					$include_match = true;
				}
			}
		}

		if ( $exclude_match ) {
			return false;
		}

		// If no include conditions exist, match everywhere (Elementor Pro behavior)
		if ( ! $has_include_conditions ) {
			return true;
		}

		if ( $include_match ) {
			return true;
		}

		return false;
	}

	private static function check_single_condition( $name, $sub_name, $sub_id ) {
		if ( 'general' === $name && '' === $sub_name ) {
			return true;
		}

		if ( 'singular' === $name || 'general' === $name && 'singular' === $sub_name ) {
			if ( is_singular() && '' === $sub_name ) {
				return true;
			}
			if ( is_singular() && 'singular' === $sub_name ) {
				return true;
			}
			if ( 'front_page' === $sub_name && is_front_page() ) {
				return true;
			}
			if ( 'post' === $sub_name && is_singular( 'post' ) ) {
				if ( ! empty( $sub_id ) ) {
					return self::get_post_sub_conditions( $sub_name, $sub_id );
				}
				return true;
			}
			if ( 'page' === $sub_name && is_singular( 'page' ) ) {
				if ( ! empty( $sub_id ) ) {
					return get_the_ID() == $sub_id;
				}
				return true;
			}
			if ( 'attachment' === $sub_name && is_attachment() ) {
				return true;
			}
			if ( 'child_of' === $sub_name && is_singular() && ! empty( $sub_id ) ) {
				$post = get_post();
				return $post && $post->post_parent == $sub_id;
			}
			if ( 'any_child_of' === $sub_name && is_singular() && ! empty( $sub_id ) ) {
				return in_array( $sub_id, get_post_ancestors( get_the_ID() ) );
			}
			if ( 'by_author' === $sub_name && is_singular() && ! empty( $sub_id ) ) {
				$post = get_post();
				return $post && $post->post_author == $sub_id;
			}
			if ( 'not_found404' === $sub_name && is_404() ) {
				return true;
			}
			if ( in_array( $sub_name, [ 'in_category', 'in_category_children', 'in_post_tag', 'post_by_author' ], true ) && is_singular( 'post' ) ) {
				return self::get_post_sub_conditions( $sub_name, $sub_id );
			}
			if ( 'page_by_author' === $sub_name && is_singular( 'page' ) && ! empty( $sub_id ) ) {
				$post = get_post();
				return $post && $post->post_author == $sub_id;
			}
		}

		if ( 'archive' === $name ) {
			if ( '' === $sub_name && ( is_archive() || is_home() || is_search() ) ) {
				return true;
			}
			if ( 'author' === $sub_name && is_author() ) {
				if ( ! empty( $sub_id ) ) {
					return is_author( $sub_id );
				}
				return true;
			}
			if ( 'date' === $sub_name && is_date() ) {
				return true;
			}
			if ( 'search' === $sub_name && is_search() ) {
				return true;
			}
			if ( 'post_archive' === $sub_name && is_home() ) {
				return true;
			}
			if ( 'category' === $sub_name && is_category() ) {
				if ( ! empty( $sub_id ) ) {
					return is_category( $sub_id );
				}
				return true;
			}
			if ( 'child_of_category' === $sub_name && is_category() && ! empty( $sub_id ) ) {
				$cat = get_queried_object();
				return $cat && $cat->parent == $sub_id;
			}
			if ( 'any_child_of_category' === $sub_name && is_category() && ! empty( $sub_id ) ) {
				$cat = get_queried_object();
				return $cat && ( $cat->parent == $sub_id || in_array( $sub_id, get_ancestors( $cat->term_id, 'category' ) ) );
			}
			if ( 'post_tag' === $sub_name && is_tag() ) {
				if ( ! empty( $sub_id ) ) {
					return is_tag( $sub_id );
				}
				return true;
			}
		}

		if ( 'woocommerce' === $name && class_exists( 'WooCommerce' ) ) {
			if ( '' === $sub_name && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
				return true;
			}
			if ( 'shop_page' === $sub_name && is_shop() ) {
				return true;
			}
			if ( 'product' === $sub_name && is_singular( 'product' ) ) {
				if ( ! empty( $sub_id ) ) {
					return self::get_product_sub_conditions( $sub_name, $sub_id );
				}
				return true;
			}
			if ( 'product_in_category' === $sub_name && is_singular( 'product' ) && ! empty( $sub_id ) ) {
				return has_term( $sub_id, 'product_cat', get_the_ID() );
			}
			if ( 'product_in_tag' === $sub_name && is_singular( 'product' ) && ! empty( $sub_id ) ) {
				return has_term( $sub_id, 'product_tag', get_the_ID() );
			}
			if ( 'product_archive' === $sub_name && is_product_category() ) {
				return true;
			}
			if ( 'product_category' === $sub_name && is_product_category() ) {
				if ( ! empty( $sub_id ) ) {
					return is_product_category( $sub_id );
				}
				return true;
			}
			if ( 'product_tag' === $sub_name && is_product_tag() ) {
				if ( ! empty( $sub_id ) ) {
					return is_product_tag( $sub_id );
				}
				return true;
			}
			if ( 'cart' === $sub_name && is_cart() ) {
				return true;
			}
			if ( 'checkout' === $sub_name && is_checkout() ) {
				return true;
			}
			if ( 'myaccount' === $sub_name && is_account_page() ) {
				return true;
			}
		}

		return false;
	}

	private static function get_post_sub_conditions( $sub_name, $sub_id ) {
		$post = get_post();
		if ( ! $post || 'post' !== $post->post_type ) {
			return false;
		}
		if ( 'post' === $sub_name && ! empty( $sub_id ) ) {
			return $post->ID == $sub_id;
		}
		if ( 'post_by_author' === $sub_name && ! empty( $sub_id ) ) {
			return $post->post_author == $sub_id;
		}
		if ( 'in_category' === $sub_name && ! empty( $sub_id ) ) {
			return has_term( $sub_id, 'category', $post->ID );
		}
		if ( 'in_category_children' === $sub_name && ! empty( $sub_id ) ) {
			$terms = wp_get_post_terms( $post->ID, 'category' );
			foreach ( $terms as $term ) {
				if ( $term->parent == $sub_id || in_array( $sub_id, get_ancestors( $term->term_id, 'category' ) ) ) {
					return true;
				}
			}
			return false;
		}
		if ( 'in_post_tag' === $sub_name && ! empty( $sub_id ) ) {
			return has_term( $sub_id, 'post_tag', $post->ID );
		}
		return false;
	}

	private static function get_product_sub_conditions( $sub_name, $sub_id ) {
		if ( 'product' === $sub_name && ! empty( $sub_id ) ) {
			return get_the_ID() == $sub_id;
		}
		return false;
	}
}