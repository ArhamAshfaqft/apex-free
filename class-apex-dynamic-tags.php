<?php
namespace ArhamAshfaq\ApexAddonsForElementor;

// phpcs:disable WordPress.Security.NonceVerification -- Request Parameter is intentionally a read-only dynamic tag for displaying sanitized GET or POST values.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- This integration intentionally applies a documented WooCommerce core filter.

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Core\DynamicTags\Tag;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;

/* ==========================================================================
   POST GROUP DYNAMIC TAGS
   ========================================================================== */

class EAS_Post_Date_Tag extends Tag {
	public function get_name() { return 'post-date'; }
	public function get_title() { return esc_html__( 'Post Date', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'post'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	protected function register_controls() {
		$this->add_control(
			'format',
			[
				'label' => esc_html__( 'Format', 'apex-addons-for-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => get_option( 'date_format' ),
			]
		);
	}

	public function render() {
		$format = $this->get_settings( 'format' );
		echo esc_html( get_the_date( $format ) );
	}
}

class EAS_Post_Excerpt_Tag extends Tag {
	public function get_name() { return 'post-excerpt'; }
	public function get_title() { return esc_html__( 'Post Excerpt', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'post'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		echo wp_kses_post( get_the_excerpt() );
	}
}

class EAS_Post_ID_Tag extends Tag {
	public function get_name() { return 'post-id'; }
	public function get_title() { return esc_html__( 'Post ID', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'post'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY, Module::NUMBER_CATEGORY ]; }

	public function render() {
		echo esc_html( (string) get_the_ID() );
	}
}

class EAS_Post_Terms_Tag extends Tag {
	public function get_name() { return 'post-terms'; }
	public function get_title() { return esc_html__( 'Post Terms', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'post'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	protected function register_controls() {
		$taxonomies = get_taxonomies( [ 'show_in_nav_menus' => true ], 'objects' );
		$options = [];
		foreach ( $taxonomies as $taxonomy ) {
			$options[ $taxonomy->name ] = $taxonomy->label;
		}
		$this->add_control(
			'taxonomy',
			[
				'label' => esc_html__( 'Taxonomy', 'apex-addons-for-elementor' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $options,
				'default' => 'category',
			]
		);
	}

	public function render() {
		$taxonomy = $this->get_settings( 'taxonomy' );
		$terms = get_the_terms( get_the_ID(), $taxonomy );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$names = wp_list_pluck( $terms, 'name' );
			echo esc_html( implode( ', ', $names ) );
		}
	}
}

class EAS_Post_Time_Tag extends Tag {
	public function get_name() { return 'post-time'; }
	public function get_title() { return esc_html__( 'Post Time', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'post'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	protected function register_controls() {
		$this->add_control(
			'format',
			[
				'label' => esc_html__( 'Format', 'apex-addons-for-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => get_option( 'time_format' ),
			]
		);
	}

	public function render() {
		$format = $this->get_settings( 'format' );
		echo esc_html( get_the_time( $format ) );
	}
}

class EAS_Post_Title_Tag extends Tag {
	public function get_name() { return 'post-title'; }
	public function get_title() { return esc_html__( 'Post Title', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'post'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		echo esc_html( get_the_title() );
	}
}

class EAS_Post_Custom_Field_Tag extends Tag {
	public function get_name() { return 'post-custom-field'; }
	public function get_title() { return esc_html__( 'Post Custom Field', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'post'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	protected function register_controls() {
		$this->add_control(
			'key',
			[
				'label' => esc_html__( 'Custom Field Key', 'apex-addons-for-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
			]
		);
	}

	public function render() {
		$key = $this->get_settings( 'key' );
		if ( ! empty( $key ) ) {
			$value = get_post_meta( get_the_ID(), sanitize_key( $key ), true );
			if ( is_scalar( $value ) ) {
				echo esc_html( (string) $value );
			}
		}
	}
}

/* ==========================================================================
   ARCHIVE GROUP DYNAMIC TAGS
   ========================================================================== */

class EAS_Archive_Description_Tag extends Tag {
	public function get_name() { return 'archive-description'; }
	public function get_title() { return esc_html__( 'Archive Description', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'archive'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		echo wp_kses_post( get_the_archive_description() );
	}
}

class EAS_Archive_Meta_Tag extends Tag {
	public function get_name() { return 'archive-meta'; }
	public function get_title() { return esc_html__( 'Archive Meta', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'archive'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	protected function register_controls() {
		$this->add_control(
			'key',
			[
				'label' => esc_html__( 'Meta Key', 'apex-addons-for-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
			]
		);
	}

	public function render() {
		$key = $this->get_settings( 'key' );
		if ( ! empty( $key ) && ( is_category() || is_tag() || is_tax() ) ) {
			$term_id = get_queried_object_id();
			$value = get_term_meta( $term_id, sanitize_key( $key ), true );
			if ( is_scalar( $value ) ) {
				echo esc_html( (string) $value );
			}
		}
	}
}

class EAS_Archive_Title_Tag extends Tag {
	public function get_name() { return 'archive-title'; }
	public function get_title() { return esc_html__( 'Archive Title', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'archive'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		echo esc_html( get_the_archive_title() );
	}
}

/* ==========================================================================
   SITE GROUP DYNAMIC TAGS
   ========================================================================== */

class EAS_Page_Title_Tag extends Tag {
	public function get_name() { return 'page-title'; }
	public function get_title() { return esc_html__( 'Page Title', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'site'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		echo esc_html( wp_get_document_title() );
	}
}

class EAS_Site_Tagline_Tag extends Tag {
	public function get_name() { return 'site-tagline'; }
	public function get_title() { return esc_html__( 'Site Tagline', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'site'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		echo esc_html( get_bloginfo( 'description' ) );
	}
}

class EAS_Site_Title_Tag extends Tag {
	public function get_name() { return 'site-title'; }
	public function get_title() { return esc_html__( 'Site Title', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'site'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		echo esc_html( get_bloginfo( 'name' ) );
	}
}

class EAS_Current_Date_Time_Tag extends Tag {
	public function get_name() { return 'current-date-time'; }
	public function get_title() { return esc_html__( 'Current Date Time', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'site'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	protected function register_controls() {
		$this->add_control(
			'format',
			[
				'label' => esc_html__( 'Format', 'apex-addons-for-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
			]
		);
	}

	public function render() {
		$format = $this->get_settings( 'format' );
		echo esc_html( current_time( $format ) );
	}
}

class EAS_Request_Arg_Tag extends Tag {
	public function get_name() { return 'request-arg'; }
	public function get_title() { return esc_html__( 'Request Parameter', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'site'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	protected function register_controls() {
		$this->add_control(
			'type',
			[
				'label' => esc_html__( 'Type', 'apex-addons-for-elementor' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'get' => 'GET',
					'post' => 'POST',
				],
				'default' => 'get',
			]
		);
		$this->add_control(
			'parameter_name',
			[
				'label' => esc_html__( 'Parameter Name', 'apex-addons-for-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
			]
		);
	}

	public function render() {
		$type = $this->get_settings( 'type' );
		$name = sanitize_key( $this->get_settings( 'parameter_name' ) );
		if ( ! empty( $name ) ) {
			$value = 'post' === $type
				? ( isset( $_POST[ $name ] ) ? map_deep( wp_unslash( $_POST[ $name ] ), 'sanitize_text_field' ) : '' )
				: ( isset( $_GET[ $name ] ) ? map_deep( wp_unslash( $_GET[ $name ] ), 'sanitize_text_field' ) : '' );
			if ( is_array( $value ) ) {
				$value = implode( ', ', array_map( 'sanitize_text_field', $value ) );
			}
			echo esc_html( sanitize_text_field( $value ) );
		}
	}
}

class EAS_Shortcode_Tag extends Tag {
	public function get_name() { return 'shortcode'; }
	public function get_title() { return esc_html__( 'Shortcode', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'site'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	protected function register_controls() {
		$this->add_control(
			'shortcode',
			[
				'label' => esc_html__( 'Shortcode', 'apex-addons-for-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
			]
		);
	}

	public function render() {
		$shortcode = $this->get_settings( 'shortcode' );
		if ( ! empty( $shortcode ) ) {
			echo wp_kses_post( do_shortcode( $shortcode ) );
		}
	}
}

class EAS_User_Info_Tag extends Tag {
	public function get_name() { return 'user-info'; }
	public function get_title() { return esc_html__( 'User Info', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'site'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	protected function register_controls() {
		$this->add_control(
			'field',
			[
				'label' => esc_html__( 'User Field', 'apex-addons-for-elementor' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'display_name' => esc_html__( 'Display Name', 'apex-addons-for-elementor' ),
					'user_login' => esc_html__( 'Username', 'apex-addons-for-elementor' ),
					'user_email' => esc_html__( 'Email', 'apex-addons-for-elementor' ),
					'description' => esc_html__( 'Bio', 'apex-addons-for-elementor' ),
				],
				'default' => 'display_name',
			]
		);
	}

	public function render() {
		$field = $this->get_settings( 'field' );
		$user = wp_get_current_user();
		if ( $user && $user->ID ) {
			if ( 'description' === $field ) {
				echo wp_kses_post( get_the_author_meta( 'description', $user->ID ) );
			} else {
				echo esc_html( $user->$field );
			}
		}
	}
}

/* ==========================================================================
   MEDIA GROUP DYNAMIC TAGS
   ========================================================================== */

class EAS_Featured_Image_Data_Tag extends Data_Tag {
	public function get_name() { return 'featured-image-data'; }
	public function get_title() { return esc_html__( 'Featured Image Data', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'media'; }
	public function get_categories() { return [ Module::IMAGE_CATEGORY ]; }

	public function get_value( array $options = [] ) {
		$id = get_post_thumbnail_id( get_the_ID() );
		if ( ! $id ) {
			return [];
		}
		$url = wp_get_attachment_image_src( $id, 'full' );
		return [
			'id' => $id,
			'url' => $url ? esc_url_raw( $url[0] ) : '',
		];
	}
}

/* ==========================================================================
   AUTHOR GROUP DYNAMIC TAGS
   ========================================================================== */

class EAS_Author_Info_Tag extends Tag {
	public function get_name() { return 'author-info'; }
	public function get_title() { return esc_html__( 'Author Info', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'author'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		echo wp_kses_post( get_the_author_meta( 'description' ) );
	}
}

class EAS_Author_Meta_Tag extends Tag {
	public function get_name() { return 'author-meta'; }
	public function get_title() { return esc_html__( 'Author Meta', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'author'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	protected function register_controls() {
		$this->add_control(
			'key',
			[
				'label' => esc_html__( 'Author Meta Key', 'apex-addons-for-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
			]
		);
	}

	public function render() {
		$key = $this->get_settings( 'key' );
		if ( ! empty( $key ) ) {
			$value = get_the_author_meta( sanitize_key( $key ) );
			if ( is_scalar( $value ) ) {
				echo esc_html( (string) $value );
			}
		}
	}
}

class EAS_Author_Name_Tag extends Tag {
	public function get_name() { return 'author-name'; }
	public function get_title() { return esc_html__( 'Author Name', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'author'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		echo esc_html( get_the_author() );
	}
}

/* ==========================================================================
   COMMENTS GROUP DYNAMIC TAGS
   ========================================================================== */

class EAS_Comments_Number_Tag extends Tag {
	public function get_name() { return 'comments-number'; }
	public function get_title() { return esc_html__( 'Comments Number', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'comments'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY, Module::NUMBER_CATEGORY ]; }

	public function render() {
		echo esc_html( (string) get_comments_number() );
	}
}

/* ==========================================================================
   WOOCOMMERCE GROUP DYNAMIC TAGS
   ========================================================================== */

class EAS_Woo_Product_Price_Tag extends Tag {
	public function get_name() { return 'woocommerce-product-price-tag'; }
	public function get_title() { return esc_html__( 'Product Price', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'woocommerce'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		if ( ! function_exists( 'wc_get_product' ) ) return;
		$product = wc_get_product( get_the_ID() );
		if ( $product ) {
			echo wp_kses_post( $product->get_price_html() );
		}
	}
}

class EAS_Woo_Product_Rating_Tag extends Tag {
	public function get_name() { return 'woocommerce-product-rating-tag'; }
	public function get_title() { return esc_html__( 'Product Rating', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'woocommerce'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		if ( ! function_exists( 'wc_get_product' ) ) return;
		$product = wc_get_product( get_the_ID() );
		if ( $product ) {
			echo wp_kses_post( $product->get_rating_html() );
		}
	}
}

class EAS_Woo_Product_Sale_Tag extends Tag {
	public function get_name() { return 'woocommerce-product-sale-tag'; }
	public function get_title() { return esc_html__( 'Product Sale', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'woocommerce'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		if ( ! function_exists( 'wc_get_product' ) ) return;
		$product = wc_get_product( get_the_ID() );
		if ( $product && $product->is_on_sale() ) {
			echo esc_html__( 'Sale!', 'apex-addons-for-elementor' );
		}
	}
}

class EAS_Woo_Product_Content_Tag extends Tag {
	public function get_name() { return 'woocommerce-product-content-tag'; }
	public function get_title() { return esc_html__( 'Product Content', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'woocommerce'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		if ( ! function_exists( 'wc_get_product' ) ) return;
		$product = wc_get_product( get_the_ID() );
		if ( $product ) {
			echo wp_kses_post( do_shortcode( get_the_content( null, false, get_the_ID() ) ) );
		}
	}
}

class EAS_Woo_Product_Short_Desc_Tag extends Tag {
	public function get_name() { return 'woocommerce-product-short-description-tag'; }
	public function get_title() { return esc_html__( 'Product Short Description', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'woocommerce'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		if ( ! function_exists( 'wc_get_product' ) ) return;
		$product = wc_get_product( get_the_ID() );
		if ( $product ) {
			echo wp_kses_post( apply_filters( 'woocommerce_short_description', $product->get_short_description() ) );
		}
	}
}

class EAS_Woo_Product_SKU_Tag extends Tag {
	public function get_name() { return 'woocommerce-product-sku-tag'; }
	public function get_title() { return esc_html__( 'Product SKU', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'woocommerce'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		if ( ! function_exists( 'wc_get_product' ) ) return;
		$product = wc_get_product( get_the_ID() );
		if ( $product ) {
			echo esc_html( $product->get_sku() );
		}
	}
}

class EAS_Woo_Product_Stock_Tag extends Tag {
	public function get_name() { return 'woocommerce-product-stock-tag'; }
	public function get_title() { return esc_html__( 'Product Stock', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'woocommerce'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		if ( ! function_exists( 'wc_get_product' ) ) return;
		$product = wc_get_product( get_the_ID() );
		if ( $product ) {
			echo wp_kses_post( wc_get_stock_html( $product ) );
		}
	}
}

class EAS_Woo_Product_Terms_Tag extends Tag {
	public function get_name() { return 'woocommerce-product-terms-tag'; }
	public function get_title() { return esc_html__( 'Product Terms', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'woocommerce'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	protected function register_controls() {
		$this->add_control(
			'taxonomy',
			[
				'label' => esc_html__( 'Taxonomy', 'apex-addons-for-elementor' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'product_cat' => esc_html__( 'Categories', 'apex-addons-for-elementor' ),
					'product_tag' => esc_html__( 'Tags', 'apex-addons-for-elementor' ),
				],
				'default' => 'product_cat',
			]
		);
	}

	public function render() {
		$taxonomy = $this->get_settings( 'taxonomy' );
		$terms = get_the_terms( get_the_ID(), $taxonomy );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$names = wp_list_pluck( $terms, 'name' );
			echo esc_html( implode( ', ', $names ) );
		}
	}
}

class EAS_Woo_Product_Title_Tag extends Tag {
	public function get_name() { return 'woocommerce-product-title-tag'; }
	public function get_title() { return esc_html__( 'Product Title', 'apex-addons-for-elementor' ); }
	public function get_group() { return 'woocommerce'; }
	public function get_categories() { return [ Module::TEXT_CATEGORY ]; }

	public function render() {
		if ( ! function_exists( 'wc_get_product' ) ) return;
		$product = wc_get_product( get_the_ID() );
		if ( $product ) {
			echo esc_html( $product->get_name() );
		}
	}
}
