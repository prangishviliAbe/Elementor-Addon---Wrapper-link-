<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main plugin class
 */
class Elementor_Wrapper_Link_Plugin {

    public function __construct() {
        add_action( 'elementor/init', [ $this, 'init_controls' ] );

        add_action( 'elementor/frontend/section/before_render', [ $this, 'before_element_render' ] );
        add_action( 'elementor/frontend/column/before_render', [ $this, 'before_element_render' ] );
        add_action( 'elementor/frontend/container/before_render', [ $this, 'before_element_render' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function init_controls() {
        add_action( 'elementor/element/section/section_advanced/after_section_end', [ $this, 'add_link_control' ], 10, 2 );
        add_action( 'elementor/element/column/section_advanced/after_section_end', [ $this, 'add_link_control' ], 10, 2 );
        add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'add_link_control' ], 10, 2 );
    }

    public function add_link_control( $element, $args ) {
        $element->start_controls_section(
            'wrapper_link_section',
            [
                'label' => __( 'Wrapper Link', 'elementor-wrapper-link' ),
                'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
            ]
        );

        $element->add_control(
            'wrapper_link_url',
            [
                'label'       => __( 'Link URL', 'elementor-wrapper-link' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'dynamic'     => [ 'active' => true ],
                'placeholder' => __( 'https://example.com ან აირჩიე დინამიური ველი', 'elementor-wrapper-link' ),
            ]
        );

        $element->add_control(
            'wrapper_link_is_external',
            [
                'label'        => __( 'Open in new tab', 'elementor-wrapper-link' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'true',
                'default'      => '',
            ]
        );

        $element->add_control(
            'wrapper_link_nofollow',
            [
                'label'        => __( 'Add nofollow', 'elementor-wrapper-link' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'true',
                'default'      => '',
            ]
        );

        $element->end_controls_section();
    }

    public function before_element_render( $element ) {
        $settings = $element->get_settings_for_display();

        $url = isset( $settings['wrapper_link_url'] ) ? $settings['wrapper_link_url'] : '';

        // If dynamic tag data, try to get actual value
        if ( is_array( $url ) && isset( $url['id'] ) ) {
            $tag = \Elementor\Plugin::instance()->dynamic_tags->get_tag_data( $url );
            if ( $tag && isset( $tag['tag'] ) && method_exists( $tag['tag'], 'get_value' ) ) {
                $url = $tag['tag']->get_value( $tag['settings'] );
            }
        }

        if ( empty( $url ) ) {
            return;
        }

        $url         = esc_url( $url );
        $is_external = ! empty( $settings['wrapper_link_is_external'] ) ? 'true' : 'false';
        $nofollow    = ! empty( $settings['wrapper_link_nofollow'] ) ? 'true' : 'false';

        $element->add_render_attribute( '_wrapper', 'data-wrapper-link-url', $url );
        $element->add_render_attribute( '_wrapper', 'data-wrapper-link-external', $is_external );
        $element->add_render_attribute( '_wrapper', 'data-wrapper-link-nofollow', $nofollow );
        $element->add_render_attribute( '_wrapper', 'class', 'wrapper-link-enabled' );
    }

    public function enqueue_assets() {
        wp_register_script( 'ewl-wrapper-link', EWL_PLUGIN_URL . 'assets/js/wrapper-link.js', [], '1.0', true );
        wp_enqueue_script( 'ewl-wrapper-link' );
    }
}
