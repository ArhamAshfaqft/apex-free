<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- The template-scope variable and global key are fully plugin-prefixed.
/**
 * Template Name: Apex Theme Builder Canvas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

$apexadfo_template_id = $GLOBALS['apexadfo_active_theme_template_id'] ?? 0;

if ( $apexadfo_template_id ) {
	echo '<div class="eas-theme-builder-content-wrap">';
	echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $apexadfo_template_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor supplies the trusted rendered template markup.
	echo '</div>';
} else {
	while ( have_posts() ) {
		the_post();
		the_content();
	}
}

get_footer();
