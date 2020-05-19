<?php
namespace ElementorRelationships\Module\Elementor_Relationships_Module;

use Elementor\Core\Base\Module;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Elementor_Relationships_Module extends Module {
		
	private $components = [];

	private function get_available_relationships(){

        $output = array();

        // Make sure again that the plugin is activated
        if ( ! class_exists('MB_Relationships_API')) {
            return $output;
        }

        $items = \MB_Relationships_API::get_all_relationships();

        foreach ($items as $key => $settings){
            $output[$key] = __( $key, 'el-rel' );
        }

	    return $output;
    }

	public function get_name() {
		return 'simple_story_post_queries';
	}


	public function add_component( $id, $instance ) {
		$this->components[ $id ] = $instance;

    }
	
	public function get_components( $id = null ) {
		if ( $id ) {
			if ( ! isset( $this->components[ $id ] ) ) {
				return null;
			}

			return $this->components[ $id ];
		}

		return $this->components;
	}
	
	public function advanced_query_options( $element, $args ) {

	    // get the available relationships options

        $element->add_control(
            'allow_private_posts',
            [
                'label'       => __( 'Allow Private posts in query', 'el-rel' ),
                'label_block' => true,
                'type'        => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Yes', 'el-rel' ),
                'label_off' => __( 'No', 'el-rel' ),
                'return_value' => 'true',
            ]
        );

	  $element->add_control(
        'advanced_query_options',
        [
          'label'       => __( 'Advanced Query Options', 'el-rel' ),
		  'label_block' => true,
          'type'        => \Elementor\Controls_Manager::SELECT2,
		  'multiple'	=> true,
 		  'options' 	=> [
              'post_relationship' => __( 'Posts with Relationships', 'el-rel' ),
		  ],	
        ]
     );

	  $element->add_control(
            'post_relationship_heading',
            [
                'label' => __( 'Relationship Details', 'el-rel' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [

                    'advanced_query_options' => 'post_relationship',
                ],
            ]
        );
        $element->add_control(
            'post_relationship_name',
            [
                'label'       => __( 'Relationship Name', 'el-rel' ),
                'label_block' => true,
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'placeholder' => 'relationships name',
                'default' => '',
                'options' => $this->get_available_relationships(),
                'description' => __( 'Enter the name of the relationship', 'el-rel' ),
                'condition' => [
                    'advanced_query_options' => 'post_relationship',
                ],
            ]
        );

        $element->add_control(
            'post_relationship_direction',
            [
                'label'       => __( 'Relationship direction', 'el-rel' ),
                'label_block' => true,
                'type'        => \Elementor\Controls_Manager::SELECT,
                'default' 	=> 'to',
                'options' => [
                    'to' => __( 'To', 'el-rel' ),
                    'from' => __( 'From', 'el-rel' ),
                ],
                'condition' => [
                    'advanced_query_options' => 'post_relationship',
                ],
            ]
        );

        $element->add_control(
            'post_relationship_sibling',
            [
                'label'       => __( 'Query Relationship Sibling', 'el-rel' ),
                'label_block' => true,
                'type'        => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Yes', 'el-rel' ),
                'label_off' => __( 'No', 'el-rel' ),
                'return_value' => 'true',
                'condition' => [
                    'advanced_query_options' => 'post_relationship',
                ],
            ]
        );

    }

	public function advanced_query_args( $query_vars, $widget ){

		$settings = $widget->get_settings();

		if ($settings['allow_private_posts']){
            $query_vars['post_status'] = array( 'publish', 'private');
        }

		if( empty( $settings[ 'advanced_query_options' ] ) ){
			return $query_vars;
		}
		
		
		$meta_query = array();
		
		if( isset( $query_vars[ 'meta_query' ] ) ) $meta_query = $query_vars[ 'meta_query' ];
		$advanced_options = $settings[ 'advanced_query_options' ];
		$post_id = get_the_ID();
		$user_field_value = '';
		global $current_user;
		
		if( $advanced_options ){

            if( in_array( 'post_relationship', $advanced_options ) && $settings[ 'post_relationship_name' ] ) {
                $query_vars['relationship'] = [
                        'id' => $settings[ 'post_relationship_name' ],
                        $settings[ 'post_relationship_direction' ] => get_the_ID(),
                        'sibling' => $settings['post_relationship_sibling'],
                    ];
            }

            $query_vars[ 'meta_query' ] = $meta_query;
			
		}

        return $query_vars;
	}
	
	public function __construct() {
		
		add_action( 'elementor/element/posts/section_query/before_section_end', [ $this, 'advanced_query_options' ], 10, 2 );
		add_action( 'elementor/element/portfolio/section_query/before_section_end', [ $this, 'advanced_query_options' ], 10, 2 );
		add_action( 'elementor/element/posts-extra/section_query/before_section_end', [ $this, 'advanced_query_options' ], 10, 2 );
		add_filter( 'elementor/query/query_args', [ $this, 'advanced_query_args' ], 10, 2 );

	}
	
}

Elementor_Relationships_Module::instance();