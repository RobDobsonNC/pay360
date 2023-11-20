<?php

namespace WcPay360\Updates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since  2.4.0
 * @author VanboDevelops
 *
 *        Copyright: (c) 2023 VanboDevelops
 *        License: GNU General Public License v3.0
 *        License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
class Version_Based_Updates {
	
	public function __construct() {
		$this->version = get_option( 'wc_pay360_version', '1.2.6' );
	}
	
	/**
	 * Loads the hooks
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'check_version_and_update' ), 0 );
	}
	
	/**
	 * Checks the version and runs the update procedure
	 *
	 * @since 2.0
	 */
	public function check_version_and_update() {
		if ( ! defined( 'IFRAME_REQUEST' ) && $this->version !== \WC_Pay360::VERSION ) {
			$this->update();
		}
	}
	
	/**
	 * Performs needed updates for the plugin for moving through version
	 *
	 * @since 2.0
	 */
	public function update() {
		// Since in 2.0 we changed the gateway ID to 'pay360', we want to copy the plugin settings to the new gateway id.
		if ( version_compare( $this->version, '2.0', '<' ) && version_compare( \WC_Pay360::VERSION, '2.0', '>=' ) ) {
			$current_settings = get_option( 'woocommerce_paypoint_settings' );
			
			update_option( 'woocommerce_pay360_settings', $current_settings );
		}
		
		if ( version_compare( $this->version, '2.4', '<' ) && version_compare( \WC_Pay360::VERSION, '2.4', '>=' ) ) {
			$current_settings = get_option( 'woocommerce_pay360_settings' );
			
			$current_settings['hosted_cashier_renewals_method'] = 'repeat';
			if ( \WC_Pay360::is_subscriptions_active() ) {
				$subscriptions = wcs_get_subscriptions( array(
					'payment_method'      => 'pay360',
					'subscription_status' => 'active',
				) );
				
				if ( $subscriptions && 0 < count( $subscriptions ) && 'live' == $current_settings['testmode'] ) {
					$current_settings['hosted_cashier_renewals_method'] = 'token';
				}
			}
			
			update_option( 'woocommerce_pay360_settings', $current_settings );
		}
		
		// Save the current version
		update_option( 'wc_pay360_version', \WC_Pay360::VERSION );
	}
}