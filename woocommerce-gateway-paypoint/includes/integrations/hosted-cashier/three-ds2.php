<?php

namespace WcPay360\Integrations\Hosted_Cashier;

use WcPay360\Helpers\Formatting;
use WcPay360\Pay360_Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since  2.3.0
 * @author VanboDevelops
 *
 *        Copyright: (c) 2021 VanboDevelops
 *        License: GNU General Public License v3.0
 *        License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
class Three_DS2 {
	
	/**
	 * @var \WC_Order
	 */
	protected $order;
	/**
	 * @var Pay360_Customer
	 */
	protected $customer;
	/**
	 * @var \WC_Payment_Gateway|\WC_Pay360_Gateway
	 */
	protected $gateway;
	/**
	 * @var \WC_Pay360_Abstract_Request
	 */
	protected $request_object;
	
	/**
	 * Three_DS2 constructor.
	 *
	 * @param                             $order
	 * @param \WC_Pay360_Abstract_Request $request_object
	 */
	public function __construct( $order, $request_object ) {
		$this->set_order( $order );
		$this->set_customer( new Pay360_Customer( $order->get_customer_id() ) );
		$this->set_request_object( $request_object );
		$this->set_gateway( $this->get_request_object()->get_gateway() );
	}
	
	public function get_order() {
		return $this->order;
	}
	
	public function get_customer() {
		return $this->customer;
	}
	
	public function get_gateway() {
		return $this->gateway;
	}
	
	public function get_request_object() {
		return $this->request_object;
	}
	
	public function set_order( $order ) {
		$this->order = $order;
	}
	
	public function set_customer( $customer ) {
		$this->customer = $customer;
	}
	
	public function set_gateway( $gateway ) {
		$this->gateway = $gateway;
	}
	
	public function set_request_object( $request_object ) {
		$this->request_object = $request_object;
	}
	
	public function add_strong_authentication_params( $params ) {
		
		if ( ! $this->get_request_object()->is_3ds_enabled( $this->get_order() ) ) {
			return $params;
		}
		
		if ( $this->get_order()->has_shipping_address() ) {
			$params['order']['shippingAddress'] = $this->get_shipping_fields();
		}
		
		// State that 3DS is enabled
		$params['transaction']['do3DSecure'] = true;
		
		// Add additional identification parameters to help with the 3DS
		$params['strongCustomerAuthentication'] = array(
			// Setting for challengeRequested
			'challengeRequested' => $this->get_challenge_requested(),
			'transactionType'    => $this->get_threeds_transaction_type(),
			'merchantRisk'       => array(
				'shippingTo' => $this->get_order()->get_shipping_address_1() == $this->get_order()->get_billing_address_1() ? 'BILLING_ADDRESS' : 'OTHER_ADDRESS',
				
				'deliveryEmail' => $this->get_order()->get_billing_email(),
			),
			
			'accountInfo' => array(
				'accountOpened' => array(
					'date' => $this->get_customer()->get_customer_account_opened_time_frame(),
				),
				
				'accountLastChanged' => array(
					'date' => $this->get_customer()->get_customer_account_updated_time_frame(),
				),
				
				'shippingAddressFirstUsed' => array(
					'date' => $this->get_customer()->get_shipping_address_first_use( $this->get_order() ),
				),
				
				'activity' => array(
					'purchasesInLastSixMonths'         => $this->get_customer()->get_successful_orders_last_six_months(),
					'transactionAttemptsInLast24Hours' => $this->get_customer()->get_orders_last_day(),
					'transactionAttemptsInLastYear'    => $this->get_customer()->get_orders_last_year(),
				),
				
				'shippingNameSameAsAccountName' => $this->get_order()->get_formatted_billing_full_name() == $this->get_order()->get_formatted_shipping_full_name(),
			),
		);
		
		return apply_filters( 'wc_pay360_hosted_cashier_additional_params', $params, $this );
	}
	
	/**
	 * @return array
	 */
	public function get_shipping_fields() {
		
		$shipping = array(
			'name'        => Formatting::format_string( $this->get_order()->get_formatted_shipping_full_name(), 255 ),
			'line1'       => Formatting::format_string( $this->get_order()->get_shipping_address_1(), 255 ),
			'line2'       => Formatting::format_string( $this->get_order()->get_shipping_address_2(), 255 ),
			'city'        => Formatting::format_string( $this->get_order()->get_shipping_city(), 255 ),
			'region'      => $this->get_request_object()->convert_state_code_to_name( \WC_Pay360_Compat::get_order_shipping_state( $this->get_order() ), \WC_Pay360_Compat::get_order_shipping_country( $this->get_order() ) ),
			'countryCode' => Formatting::format_string( $this->get_request_object()->convert_country_code_to_alpha_3( \WC_Pay360_Compat::get_order_shipping_country( $this->get_order() ) ), 3 ),
			'postcode'    => Formatting::format_string( $this->get_order()->get_shipping_postcode(), 255 ),
		);
		
		// Remove empty elements
		$shipping = array_filter( $shipping );
		
		return $shipping;
	}
	
	public function get_challenge_requested() {
		return apply_filters( 'wc_pay360_hosted_cashier_challenge_requested', $this->get_gateway()->get_option( 'cashier_challenge_requested', 'NO_PREFERENCE' ), $this->get_order() );
	}
	
	public function get_threeds_transaction_type() {
		return apply_filters( 'wc_pay360_hosted_cashier_threed_transaction_type', 'GOODS_OR_SERVICES', $this->get_order() );
	}
}