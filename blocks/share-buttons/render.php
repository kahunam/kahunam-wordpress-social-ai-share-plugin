<?php
/**
 * AI Share Buttons Block - Server-side render
 *
 * @package kaais
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get wrapper attributes for block supports (alignment, spacing, etc.)
$wrapper_attributes = get_block_wrapper_attributes();

// Render the share buttons
$buttons = kaais_render_buttons();

if ( empty( $buttons ) ) {
    return;
}

// Wrap in block container with wrapper attributes
printf(
    '<div %s>%s</div>',
    $wrapper_attributes,
    $buttons
);
