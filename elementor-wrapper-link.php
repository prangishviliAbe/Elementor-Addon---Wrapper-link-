<?php
/**
 * Plugin Name: Elementor Wrapper Link (Section, Column, Container)
 * Description: დაამატე ბმული Elementor-ის სექციებს, კოლონებს და კონტეინერებს. გახადე დაკლიკებადი wrapper ელემენტები.
 * Version: 1.0
 * Author: აბე ფრანგიშვილი
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Elementor_Wrapper_Link {

    public function __construct() {
        // კონტროლის დამატება ელემენტებზე
        add_action( 'elementor/init', [ $this, 'init_controls' ] );

        // რენდერის წინ ატრიბუტების დამატება
        add_action( 'elementor/frontend/section/before_render', [ $this, 'before_element_render' ] );
        add_action( 'elementor/frontend/column/before_render', [ $this, 'before_element_render' ] );
        add_action( 'elementor/frontend/container/before_render', [ $this, 'before_element_render' ] );

        add_action( 'wp_footer', [ $this, 'enqueue_script' ] );
    }

    public function init_controls() {
        //Advanced ან Layout tab-ის ბოლოს
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
                'type'        => \Elementor\Controls_Manager::URL,
                'placeholder' => __( 'https://example.com', 'elementor-wrapper-link' ),
                'show_external' => true,
            ]
        );

        $element->end_controls_section();
    }

    public function before_element_render( $element ) {
        $settings = $element->get_settings_for_display();

        if ( empty( $settings['wrapper_link_url']['url'] ) ) {
            return;
        }

        $url         = esc_url( $settings['wrapper_link_url']['url'] );
        $is_external = !empty( $settings['wrapper_link_url']['is_external'] ) ? 'true' : 'false';
        $nofollow    = !empty( $settings['wrapper_link_url']['nofollow'] ) ? 'true' : 'false';

        $element->add_render_attribute( '_wrapper', 'data-wrapper-link-url', $url );
        $element->add_render_attribute( '_wrapper', 'data-wrapper-link-external', $is_external );
        $element->add_render_attribute( '_wrapper', 'data-wrapper-link-nofollow', $nofollow );
        $element->add_render_attribute( '_wrapper', 'class', 'wrapper-link-enabled' );
    }

    public function enqueue_script() {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.wrapper-link-enabled').forEach(function (el) {
                const url = el.dataset.wrapperLinkUrl;
                const isExternal = el.dataset.wrapperLinkExternal === 'true';
                const nofollow = el.dataset.wrapperLinkNofollow === 'true';

                if (url) {
                    el.style.cursor = 'pointer';

                    el.addEventListener('click', function (e) {
                        if (e.target.closest('a, button, input, textarea')) return;

                        const a = document.createElement('a');
                        a.href = url;
                        if (isExternal) a.target = '_blank';
                        if (nofollow) a.rel = 'nofollow';
                        a.style.display = 'none';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                    });
                }
            });
        });
        </script>
        <?php
    }
}

new Elementor_Wrapper_Link();
