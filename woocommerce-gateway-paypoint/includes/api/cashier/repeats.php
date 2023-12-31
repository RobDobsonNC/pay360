<?php

namespace WcPay360\Api\Cashier;

use WcPay360\Api\Exceptions\Exception;
use WcPay360\Api\Exceptions\Invalid_Argument;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Repeat payment requests
 *
 * @since  2.4.0
 * @author VanboDevelops | Ivan Andreev
 *
 *        Copyright: (c) 2023 VanboDevelops
 *        License: GNU General Public License v3.0
 *        License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
class Repeats implements Cashier_Process_Interface {
	
	/**
	 * @var Cashier_Service
	 */
	protected $service;
	/**
	 * @var string Transaction ID
	 */
	protected $transaction_id;
	/**
	 * @var string Merchant Reference ID
	 */
	protected $merchant_reference_id;
	
	/**
	 * Captures constructor.
	 *
	 * @param Cashier_Service $service
	 */
	public function __construct( Cashier_Service $service ) {
		$this->service = $service;
	}
	
	/**
	 * Returns the resource part of the request URL
	 *
	 * @since 2.1
	 *
	 * @return string
	 * @throws Invalid_Argument
	 */
	public function get_resource_endpoint() {
		return '/acceptor/rest/transactions/' . $this->service->get_client()->get_installation();
	}
	
	public function repeat_payment( $transaction_id, $arguments = array() ) {
		$url = $this->get_resource_endpoint() . '/' . $transaction_id . $this->service->get_action_endpoint( 'repeat' );
		
		$transaction = $this->service->get_client()->send_request( $url, $arguments, 'POST' );
		
		$this->service->get_client()->check_response_code( $transaction, 'cashier_payments' );
		
		return $transaction;
	}
}