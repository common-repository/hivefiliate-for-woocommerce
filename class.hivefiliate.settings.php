<?php
class HivefiliateSettings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;


    public function __construct()
    {
        $options = get_option('hivefiliate_option_setting');

    }

    /**
    * check if plugin woocommerce is activated
    */
    public static function checking_woocommerce()
    {
      if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

        // show message if not activated and installed
        wp_die('Sorry, but this plugin requires the woocommerce to be installed and active. <br><a href="' . admin_url('plugins.php') . '">&laquo; Return to Plugins</a>');

      }
    }


    /**
    * disconnect reference on hivefiliate when plugin deactivated
    */
    public static function deactivation()
    {

        // get host url for reference
        $domain_url            = get_bloginfo('url');

        $post     = array(
            'type'             => 'deactivate',
            'domain_url'       => $domain_url
        );

        // send data via wp remote
        $result = HivefiliateTracking::WPbackend_remote($post);
        flush_rewrite_rules();

    }



    /**
    * add setting url
    */
    public static function add_settings($links)
    {
      $url = get_admin_url() . "admin.php?page=hivefiliate-admin-setting";
      $link = '<a href="' . $url . '">Settings</a>';
      array_unshift($links, $link);

      return $links;
    }

    /**
     * Add options page
     */
    public static function add_plugin_page()
    {
        // This page will be under "Settings"
        add_submenu_page( 'woocommerce', 'Hivefiliate', 'Hivefiliate', 'manage_options', 'hivefiliate-admin-setting', array('HivefiliateSettings', 'create_submenu_page_admin') );
    }




    /**
     * Options page callback
     */
    public static function create_submenu_page_admin()
    {

        if (!current_user_can('manage_options')) {
          wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }


        // Check if inputed api is valid
        $option           = get_option('hivefiliate_option_setting');

        // Get host url for reference
        $domain_url       = get_bloginfo('url');

        $post     = array(
            'type'             => 'check_api',
            'domain_url'       => $domain_url
        );

        // Send data via wp remote
        $result = HivefiliateTracking::WPbackend_remote($post);
        $response   = json_decode($result, true);

        if($response['data']==0){

          // Show warning message if api key is not from the hivefiliate account
          echo '<div class="notice notice-warning"><p>';
          _e(' Your almost done! In order to work properly, Please kindly enter your valid api key from your hivefiliate account.');
          echo '</p></div>';

        }

        // Success message after updated
        if (isset($_GET['settings-updated'])) {
          echo '<div class="notice notice-success is-dismissible"><p>';
          _e(' Settings successfully saved.');
          echo '</p></div>';
        }

        ?>
        <div class="wrap">
            <img width="200" src="<?php echo plugins_url('logo.jpg', __FILE__); ?>" alt="Hivefiliate"/>
            <h2>
              <?php _e('Everything You Need To Manage Your Affiliate marketing','hivefiliate-for-woocommerce')?>
            </h2>
            <p>
              <?php _e('Grow your affiliate marketing by managing them all on a single flexible, intuitive platform. Create your own affiliate program in minutes. 0% transaction fees. Reward, track &amp; incentivize the affiliates you choose.','hivefiliate-for-woocommerce')?>
            </p>
            <p>
              <?php _e('This plugin require Hivefiliate api key. In order to connect and acquire api key, You must have an Hivefiliate <a href="https://hivefiliate.com/signup/woocommerce" target="_blank">Account</a>. For more info, visit the Hivefiliate website <a href="https://hivefiliate.com" target="_blank">here</a>.', 'hivefiliate-for-woocommerce'); ?>
            </p>
            <form method="post" action="options.php">
            <?php
                settings_fields( 'hivefiliate_option_group' );
                do_settings_sections( 'hivefiliate-admin-setting' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public static function page_init()
    {
        register_setting(
            'hivefiliate_option_group',
            'hivefiliate_option_setting',
            array( 'HivefiliateSettings', 'sanitize' )
        );

        add_settings_section(
            'setting_section_id',
            'Settings',
            array( 'HivefiliateSettings', 'print_section_info' ),
            'hivefiliate-admin-setting'
        );


        add_settings_field(
            'hivefiliate_public_key',
            'Your Hivefiliate Public API Key',
            array( 'HivefiliateSettings', 'public_key_callback' ),
            'hivefiliate-admin-setting',
            'setting_section_id'
        );

        add_settings_field(
            'hivefiliate_secret_key',
            'Your Hivefiliate Secret API Key',
            array( 'HivefiliateSettings', 'secret_key_callback' ),
            'hivefiliate-admin-setting',
            'setting_section_id'
        );

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public static function sanitize( $input )
    {
        $new_input = array();

        if( isset( $input['hivefiliate_public_key'] ) )
            $new_input['hivefiliate_public_key'] = trim( $input['hivefiliate_public_key'] );

        if( isset( $input['hivefiliate_secret_key'] ) )
            $new_input['hivefiliate_secret_key'] = trim( $input['hivefiliate_secret_key'] );

        return $new_input;
    }



    /**
     * Print the Section text
     */
    public static function print_section_info()
    {
        print 'Enter your public and secret api key below:';
    }



    /**
     * Get the settings option array and print one of its values
     */
     public static function secret_key_callback()
     {

         $option = get_option('hivefiliate_option_setting');

         printf(
             '<input type="text" id="hivefiliate_secret_key" name="hivefiliate_option_setting[hivefiliate_secret_key]" value="%s" style="width:400px;"/>',
             isset( $option['hivefiliate_secret_key'] ) ? esc_attr( $option['hivefiliate_secret_key'] ) : ''
         );
     }


    /**
     * Get the settings option array and print one of its values
     */
    public static function public_key_callback()
    {
         $option = get_option('hivefiliate_option_setting');

          printf(
              '<input type="text" id="hivefiliate_public_key" name="hivefiliate_option_setting[hivefiliate_public_key]" value="%s" style="width:400px;"/>',
              isset( $option['hivefiliate_public_key'] ) ? esc_attr( $option['hivefiliate_public_key'] ) : ''
          );
    }
}
