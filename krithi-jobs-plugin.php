  <?php
  /**
  * @package CutomJobsPlugin
  */
  /*
   * Plugin Name:       Jobs Plugin
   * Plugin URI:        https://example.com/plugins/the-basics/
   * Description:       Handle the basics with this plugin.
   * Version:           1.10.3
   * Requires at least: 5.2
   * Requires PHP:      7.2
   * Author:            Krithi Krishna
   * Author URI:        https://author.example.com/
   * License:           GPL v2 or later
   * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
   * Text Domain:       my-plugin
   * Domain Path:       /languages
   */
   /*
   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

   Copyright 2005-2015 Automattic, Inc.
   */
   defined( 'ABSPATH' ) or die('Hey,you can\t access this file!' );


  if( !class_exists( 'CutomJobsPlugin' ) ) {

    class CutomJobsPlugin {

      public function __construct() {
        add_action( 'init', array( $this,'custom_post_type') );
        add_action( 'init', array( $this,'aspirant_custom_post') );
        add_action( 'admin_init', array($this,'myplugin_settings_init' ) );
        add_action( 'admin_menu', array($this,'add_tutorial_cpt_submenu_example') );
        add_action( 'admin_enqueue_scripts', array( $this,'jobs_load_jquery_datepicker') );
        add_action( 'wp_enqueue_scripts', array($this,'jobs_style' )  );
        add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
        add_action( 'add_meta_boxes', array( $this,'jobs_add_expiry_date_metabox') );
        add_action( 'save_post', array( $this,'jobs_save_expiry_date' ) );
        add_action( 'add_meta_boxes', array( $this,'jobs_add_metabox') );
        add_action( 'save_post', array( $this,'save_jobs_meta' ) );
        add_action( 'wp_ajax_save_post_details_form',array($this,'save_enquiry_form_action') );
        add_action( 'wp_ajax_my_delete_post', array($this, 'my_delete_post' ) );
        add_filter( 'the_content',array($this,'job_display_post'));
        add_filter( 'the_content', array( $this, 'report_button' ) );
        add_filter( 'the_content', array( $this, 'job_post' ), 10, 1 );
      }

      function scripts() {

          wp_enqueue_style( 'report-a-bug', plugin_dir_url( __FILE__ ) . 'style.css' );
          wp_enqueue_script( 'report-a-bug', plugin_dir_url( __FILE__ ) . 'my-script.js', array( 'jquery' ), null, true );
          //wp_enqueue_style( 'report-a-bug', 'https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js' );
          wp_localize_script( 'report-a-bug', 'settings', array('ajaxurl' => admin_url( 'admin-ajax.php' ),) );
      }

      function jobs_load_jquery_datepicker() {
          wp_enqueue_script( 'jquery-ui-datepicker', plugin_dir_url( __FILE__ ) . 'datepic.js');
          wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
      }

      function jobs_style() {
        wp_enqueue_style( 'button-demo', plugin_dir_url( __FILE__ ) . 'style.css' );
      }

      //submenu function
      function add_tutorial_cpt_submenu_example(){

           add_submenu_page(
                           'edit.php?post_type=jobs', //$parent_slug
                           'Jobs Settings page',  //$page_title
                           'Settings',        //$menu_title
                           'manage_options',           //$capability
                           'myplugin-settings-page',//$menu_slug
                           array($this,'myplugin_settings_template_callback')//$function
           );

      }



    /**
     * Settings Template Page
     */
    function myplugin_settings_template_callback() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <form action="options.php" method="post">
                <?php
                    //pass slug name of page, also referred to in Settings API as option group name
                    settings_fields( 'myplugin-settings-page' );

                    // output settings section here
                    do_settings_sections('myplugin-settings-page');

                    // save settings button
                    submit_button( 'Save Settings' );
                ?>
            </form>
        </div>
        <?php
    }


    public function report_button( $content ) {

        // display button only on posts
        global $post;
    		$slug = "jobs";
    		if($slug != $post->post_type){
    			return $content;
        }

        $content .= '<div class="report-a-bug">

                        <button class="show-form" data-post_id="' . get_the_ID() . '">' .
                            __( 'Apply Job', 'reportabug' ) .
                        '</button>

                          <div id="message" class="report-a-bug-message">


  		<form id="enquiry_email_form" action="#" method="POST">
      <h2 id=post_title>' . get_the_title() . '</h2>
  			<div class="form-group">
  				<label for="">Enter Name</label>
  				<input type="text" class="form-control" name="name" id="name" placeholder="Enter Name" />
  			</div>
        <div class="form-group">
  				<label for="">Enter Email</label>
  				<input type="text" class="form-control" name="email" id="email" placeholder="Enter Email" />
  			</div>
        <div class="form-group">
  				<label for="">Enter Phone</label>
  				<input type="text" class="form-control" name="phone" id="phone" placeholder="Enter phone" />
  			</div>
        <div class="form-group">
  				<label for="">Enter Education</label>
  				<input type="text" class="form-control" name="edu" id="edu" placeholder="Enter Education" />
  			</div>
  			<div class="form-group">
  				<button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-pencil"></i> Submit</button>
  			</div>
  		</form>
  	</div>

    </div>';

        return $content;

    }

    function aspirant_custom_post() {

	    $supports = array(
    		'title', // post title
		    'editor', // post content
		    'author', // post author
		    'thumbnail', // featured images
		    'comments', // post comments
		    'revisions', // post revisions
		    'post-formats', // post formats

		);

    	$args = array(
    			'label'        => 'Job Aspirants',
	            'public'       => true,
	            'supports'     => $supports,
	    );

	    register_post_type( 'aspirants', $args );
	   }

    function save_enquiry_form_action() {

      global $wpdb;
      $post_title = $_POST['post_details']['title'];
      $post_name = $_POST['post_details']['name'];
      $post_email = $_POST['post_details']['email'];
      $post_phone = $_POST['post_details']['phone'];
      $post_edu = $_POST['post_details']['edu'];

    $data_array = [
          'name' => $post_name,
          'email' => $post_email,
          'phone' => $post_phone,
          'edu' => $post_edu
    ];
    $m_data=array(
     			'email' => $post_email,
     			'phone' => $post_phone,
     			'edu' => $post_edu,
     		);

    $new_post = array(
                  'post_status' => 'publish',
                  'post_title' =>  $post_title,
                  'post_content' => $post_name,
                  'post_type' => 'aspirants',
                  'meta_input' => array(
                  	'content_update' =>$m_data)
                );
    $post_id = wp_insert_post($new_post);
    $table_name = 'wp_apply_job';
    $rowResult =  $wpdb->insert($table_name, $data_array, $format=NULL);

  	if($rowResult) {
      wp_send_json_success($data_array);
  	} else {
  		return "failed";
  	}
  }

  function job_post( $content ) {
  		global $post;
  		$slug = "aspirants";
  		if($slug != $post->post_type){
  			return $content;
  		}

  		$other_content = esc_attr(get_post_meta($post->ID, $m_data, true));
  		$post1 = "<p><b><i><div class = 'display_cpost'>$other_content </div>";
  	  $button='<div class="delete_post">
  		<button class="delete_app" id ="delete_app" data-post_id="' . get_the_ID() . '" data-id="'.get_the_ID(). '"  class="delete-post">DELETE</button>';
  		 return $content . $post1 .$button ;
  	}


  	function my_delete_post(){
  		wp_delete_post( $_REQUEST['id'] );
  		wp_send_json('success');
          die();
      }


    /**
     * Settings Template
     */
    function myplugin_settings_init() {

        // Setup settings section
        add_settings_section(
            'myplugin_settings_section',
            'General',
            '',
            'myplugin-settings-page'
        );

        // Register input field
        register_setting(
            'myplugin-settings-page',
            'myplugin_settings_input_field',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Add text fields
        add_settings_field(
            'myplugin_settings_input_field',
            __( 'Organization Name', 'my-plugin' ),
            array($this,'myplugin_settings_input_field_callback'),
            'myplugin-settings-page',
            'myplugin_settings_section'
        );

        // Register textarea field
        register_setting(
            'myplugin-settings-page',
            'myplugin_settings_textarea_field',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
                'default' => ''
            )
        );

         // Add textarea fields
         add_settings_field(
            'myplugin_settings_textarea_field',
            __( 'Content', 'my-plugin' ),
            array($this,'myplugin_settings_textarea_field_callback'),
            'myplugin-settings-page',
            'myplugin_settings_section'
        );

        // Register select option field
        register_setting(
            'myplugin-settings-page',
            'myplugin_settings_select_field',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Add select fields
        add_settings_field(
            'myplugin_settings_select_field',
            __( 'Number of Jobs', 'my-plugin' ),
            array($this,'myplugin_settings_select_field_callback'),
            'myplugin-settings-page',
            'myplugin_settings_section'
        );

        // Register radio field
        register_setting(
            'myplugin-settings-page',
            'myplugin_settings_radio_field',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Add radio fields
        add_settings_field(
            'myplugin_settings_radio_field',
            __( 'Show Title', 'my-plugin' ),
            array($this,'myplugin_settings_radio_field_callback'),
            'myplugin-settings-page',
            'myplugin_settings_section'
        );

        // Register checkbox fields
        register_setting(
            'myplugin-settings-page',
            'myplugin_settings_checkbox_field',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default' => ''
            )
        );

        // Add checkbox fields
        add_settings_field(
            'myplugin_settings_checkbox_field',
            __( 'Show Email', 'my-plugin' ),
            array($this,'myplugin_settings_checkbox_field_callback'),
            'myplugin-settings-page',
            'myplugin_settings_section'
        );

        // Register date field
        register_setting(
            'myplugin-settings-page',
            'myplugin_settings_date_field',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default' => ''
            )
        );

        // Add date fields
        add_settings_field(
            'myplugin_settings_date_field',
            __( 'Expiry Date', 'my-plugin' ),
            array($this,'myplugin_settings_date_field_callback'),
            'myplugin-settings-page',
            'myplugin_settings_section'
        );

        // Register color picker
        register_setting(
            'myplugin-settings-page',
            'myplugin_settings_color_field',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default' => ''
            )
        );

        // Add color fields
        add_settings_field(
            'myplugin_settings_color_field',
            __( 'Color', 'my-plugin' ),
            array($this,'myplugin_settings_color_field_callback'),
            'myplugin-settings-page',
            'myplugin_settings_section'
        );

    }


    /**
     * txt template
     */
    function myplugin_settings_input_field_callback() {
        $myplugin_input_field = get_option('myplugin_settings_input_field');
        ?>
        <input type="text" name="myplugin_settings_input_field" class="regular-text" value="<?php echo isset($myplugin_input_field) ? esc_attr( $myplugin_input_field ) : ''; ?>" />
        <?php
    }

    /**
     * date template
     */
    function myplugin_settings_date_field_callback() {

            //retrieve metadata value if it exists
            $myplugin_date_field = get_option('myplugin_settings_date_field');
            ?>

            <label for "jobs_expiry_date"><?php ('Expiry Date'); ?></label>

            <input type="text" class="MyDate" name="myplugin_settings_date_field" value=<?php echo esc_attr( $myplugin_date_field ); ?> >

      <?php
    }


    /**
     * textarea template
     */
    function myplugin_settings_textarea_field_callback() {
        $myplugin_textarea_field = get_option('myplugin_settings_textarea_field');
        ?>
        <textarea name="myplugin_settings_textarea_field" class="regular-text" rows="5"><?php echo isset($myplugin_textarea_field) ? esc_textarea( $myplugin_textarea_field ) : ''; ?></textarea>
        <?php
    }

    /**
     * select template
     */
    function myplugin_settings_select_field_callback() {
        $myplugin_select_field = get_option('myplugin_settings_select_field');
        ?>
        <select name="myplugin_settings_select_field" class="regular-text">
            <option value="">Select Vacancies</option>
            <option value="4" <?php selected( '4', $myplugin_select_field ); ?> >4</option>
            <option value="5" <?php selected( '5', $myplugin_select_field ); ?>>5</option>
        </select>
        <?php
    }

    /**
     * radio field tempalte
     */
    function myplugin_settings_radio_field_callback() {
        $myplugin_radio_field = get_option( 'myplugin_settings_radio_field' );
        ?>
        <label for="first-value">
            <input type="radio" name="myplugin_settings_radio_field" value="first-value" <?php checked( 'first-value', $myplugin_radio_field ); ?>/> show title only
        </label>
        <label for="second-value">
            <input type="radio" name="myplugin_settings_radio_field" value="second-value" <?php checked( 'second-value', $myplugin_radio_field ); ?>/> show title & content
        </label>
        <?php
    }

    /**
     * Chekcbox Tempalte
     */
    function myplugin_settings_checkbox_field_callback() {
        $myplugin_checkbox_field = get_option('myplugin_settings_checkbox_field');
        ?>
        <label for="myplugin_settings_checkbox_field"></label>
        <?php

            if($myplugin_checkbox_field == "") {
                ?>
                    <input name="myplugin_settings_checkbox_field" type="checkbox" value="true">

                <?php
            } else if($myplugin_checkbox_field == "true") {
                ?>
                    <input name="myplugin_settings_checkbox_field" type="checkbox" value="true" checked>
                    <?php
            }
      }

      //here ends settings page

      //metabox for date
    function jobs_add_expiry_date_metabox() {
      add_meta_box(
          'jobs_expiry_date_metabox',
           __( 'Custom Expiry Date', 'jobexp'),
           array($this,'jobs_expiry_date_metabox_callback'),
           'jobs',
           'side',
           'core'
      );
    }
      //callback function of date metabox
    function jobs_expiry_date_metabox_callback( $post ) {
            //retrieve metadata value if it exists
        $jobs_expiry_date = get_post_meta( $post->ID, 'expires', true );
        ?>

        <label for "jobs_expiry_date"><?php ('Expiry Date'); ?></label>
       <input type="text" class="MyDate" name="jobs_expiry_date" value=<?php echo esc_attr( $jobs_expiry_date ); ?> >
    <?php
    }
    //function to save date in database
    function jobs_save_expiry_date( $post_id ) {

        // Check if the current user has permission to edit the post. */
        // if ( !current_user_can( 'edit_post', $post_id->ID ) ) {
        // return;
        // }

        if ( isset( $_POST['jobs_expiry_date'] ) ) {
            $new_expiry_date = ( $_POST['jobs_expiry_date'] );
            update_post_meta( $post_id, 'expires', $new_expiry_date );
        }
    }

    //metabox for title and email

    function jobs_add_metabox() {
        add_meta_box(
            'jobs_metabox',
            __( 'Custom Meta Box', 'jobs-meta'),
            array($this,'jobs_metabox_callback'),
            'jobs',
            'side',
            'core',
        );
    }
    //callback function
    function jobs_metabox_callback( $post ) {
        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'notice_nonce', 'notice_nonce' );

        $title_meta = get_post_meta( $post->ID, '_job-title', true );
        $email_meta = get_post_meta( $post->ID, '_job-email', true );
        write_log($email_meta);
        ?>
          <p><label for="meta-box-title">Title</label>
          <input type="text" name="job-title" value="<?php echo esc_html( $title_meta ); ?>"></p>

          <p><label for="meta-box-email">Email</label>
          <input type="email" name="job-email" value="<?php echo esc_html( $email_meta ); ?>"></p>
          <?php
    }

    /**
     * When the post is saved, saves our custom data.
     *
     * @param int $post_id
     */
    function save_jobs_meta( $post_id ) {

        $nonce = isset( $_POST['notice_nonce']) ?  $_POST['notice_nonce'] : false;

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce($nonce, 'notice_nonce' ) ) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check the user's permissions.
        if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return;
            }

        } else {

            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }


        // Make sure that it is set.
        if ( ! isset( $_POST['job-title'] ) ) {
            return;
        }

        // Sanitize user input.
        $title_sanitize = sanitize_text_field( $_POST['job-title'] );

        // Update the meta field in the database.
        update_post_meta( $post_id, '_job-title', $title_sanitize );

        if ( ! isset( $_POST['job-email'] ) ) {
            return;
        }

        // Sanitize user input.
        $email_sanitize = sanitize_email( $_POST['job-email'] );

        // Update the meta field in the database.
        update_post_meta( $post_id, '_job-email', $email_sanitize );
    }

    //Display content in frontend
    function job_display_post( $msg ) {

        global $post;
        $cust_post_type = "jobs";
        if($cust_post_type != $post->post_type){
          return $msg;
        }
        $title_meta = esc_attr( get_post_meta( $post->ID, '_job-title', true ) );
        $date = esc_attr( get_post_meta( $post->ID, 'expires', true ) );
        $myplugin_checkbox_field = get_option('myplugin_settings_checkbox_field');
        $myplugin_input_field = get_option('myplugin_settings_input_field');
        $myplugin_textarea_field = get_option('myplugin_settings_textarea_field');
        $myplugin_radio_field = get_option( 'myplugin_settings_radio_field' );
        $myplugin_select_field = get_option('myplugin_settings_select_field');
        $myplugin_date_field = get_option('myplugin_settings_date_field');

        $input = "<div class='sp_text'><b>Company Name</b> : $myplugin_input_field</div>";
        $textarea = "<div class='sp_textarea'><b>Description </b>: $myplugin_textarea_field</div>";
        $select = "<div class='sp_select'><b>Vacancies </b>: $myplugin_select_field</div>";
        $choose_title = "<div class='sp_title_meta'><b>Job Title </b>: $title_meta</div>";
        $choose_date = "<div class='sp_obs_expiry_date_meta'><b>Expiry Date </b>: $date</div>";

        if ($myplugin_checkbox_field == "true"){
            $email_meta = esc_attr( get_post_meta( $post->ID, '_job-email', true ) );
            $choose_email = "<div class='sp_email_meta'><b>Email to send </b>: $email_meta</div>";
        } else {
            $choose_email = "";
        }

        if ($myplugin_radio_field == "first-value") {
            return $choose_title;
        } else {

          if($date < $myplugin_date_field) {
            return $date_choose = "<div class='sp_obs_expiry_date_meta'><b>Expired at</b>: $date</div>";

          } else {
            return $msg . $input . $choose_title . $choose_email .  $choose_date . $textarea . $select;
          }

        }


    }

      function activate() {
        $this->custom_post_type();
        flush_rewrite_rules();
      }

      function deactivate() {
        flush_rewrite_rules();
      }


      function custom_post_type() {
        register_post_type( 'jobs', ['public' => true, 'label' => 'Jobs','supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments')] );
      }
    }

    $cutomJobsPlugin = new CutomJobsPlugin();

  //plugin_activation

    register_activation_hook(__FILE__ , array( $cutomJobsPlugin, 'activate' ) );

  //plugin_deactivation

    register_deactivation_hook(__FILE__ , array( $cutomJobsPlugin, 'deactivate' ) );

}

    if (!function_exists('write_log')) {
    	function write_log ( $log )  {
    		if ( true === WP_DEBUG ) {
    			if ( is_array( $log ) || is_object( $log ) ) {
    				error_log( print_r( $log, true ) );
    			} else {
    				error_log( $log );
    			}
    		}
    	}
    }
