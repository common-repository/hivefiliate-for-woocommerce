<?php
class HivefiliateTracking
{


  /**
  * WP remote post
  */

  public static function WPbackend_remote($data)
  {

    $options = get_option('hivefiliate_option_setting');

    $post_data = array(
      'headers' => array(
        'Content-Type' => 'application/json',
        'Hivefiliate-Public-Key' => $options['hivefiliate_public_key'],
        'Hivefiliate-Secret-Key' => $options['hivefiliate_secret_key'],
      ),'body' => json_encode($data)
    );

    // Send order data to Hivefiliate via WP remote post
    $response = wp_remote_post('https://hivefiliate.com/api/app/woo/tracking.php', $post_data);
    return $response['body'];
  }

  /**
  * get current ip address
  */

  public static function currentipaddress(){

      if(!empty($_SERVER['HTTP_CLIENT_IP'])){
          $ip = $_SERVER['HTTP_CLIENT_IP'];
      }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
          $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      }else{
          $ip = $_SERVER['REMOTE_ADDR'];
      }
      return $ip;

  }


  /**
  * Check if affiliate id is set on referal url
  */

  public static function affiliateid( ){

      // get and check affiliate id
      $aff_id           = isset($_GET['aff']) ? (int)$_GET['aff'] : "";

      // get host url for reference
      $domain_url       = get_bloginfo('url');

      // get user ip address for reference
      $user_ip          = self::currentipaddress();

      if(empty($aff_id)||$aff_id==0){
        return;
      }

      $post = array(
          'type'        => 'checkaff_id',
          'domain_url'  => $domain_url,
          'user_ip'     => $user_ip,
          'aff_id'      => $aff_id,
      );

      // send info to hivefiliate via WP Remote host
      $response    = self::WPbackend_remote($post);


  }



  /**
  * Track order
  */
  public static function trackorder( $order_id ) {

  	if ( $order_id > 0 ) {

      // Get order object
  		$order = wc_get_order( $order_id );
  		if ( $order instanceof WC_Order ) {

        // Order ID
  			$order_id                 = $order->get_id();

        // Order key
  			$order_key                = $order->get_order_key();

        // Order total
  			$order_total              = $order->get_total();

        // Get order meta object
        $order_meta               = get_post_meta($order_id);

        // Get discount total
        $discount                 = round($order_meta['_cart_discount'][0], 2);

        // Get coupon
        $coupons                  = $order->get_coupon_codes();

        // Get customer user agent
        $user_agent               = $order_meta['_customer_user_agent'][0];

        // Get customer ip
        $customer_ip              = $order_meta['_customer_ip_address'][0];

        // Get url host
        $domain_url               = get_bloginfo('url');

        // Get coupon if order has
        if(isset($coupons[0])){
          $coupon                 = $coupons[0];
          if (!$coupon) {
              $coupon = "";
          }
          if ($discount == 0) {
              $discount = "";
          } else {
              $discount = $discount;
          }
        }else{
          $coupon                  = null;
          $discount                = null;
        }


        $post = array(
            'type'            => 'order',
            'order_id'        => $order_id,
            'order_key'       => $order_key,
            'order_total'     => $order_total,
            'coupon'          => $coupon,
            'discount'        => $discount,
            'user_agent'      => $user_agent,
            'order'           => $order,
            'domain_url'      => $domain_url,
            'user_ip'         => $customer_ip,
            'order_meta'      => $order_meta,
            'order'           => $order,
        );

        // send order to hivefiliate via WP Remote host
        $response         = self::WPbackend_remote($post);

  		}
  	}
  }



}
