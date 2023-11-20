<?php

namespace WcPay360;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Description
 *
 * @since  1.7.0
 * @author VanboDevelops
 *
 *        Copyright: (c) 2019 VanboDevelops
 *        License: GNU General Public License v3.0
 *        License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
class Pay360_Customer {
	
	protected $customer;
	
	public function __construct( $customer_id ) {
		$this->customer = new \WC_Customer( (int) $customer_id );
	}
	
	public function get_customer() {
		return $this->customer;
	}
	
	public function get_date_created() {
		return $this->get_customer()->get_date_created();
	}
	
	public function get_last_updated() {
		return $this->get_customer()->get_date_modified();
	}
	
	public function get_customer_account_opened_time_frame() {
		$date_created = $this->get_date_created();
		
		return $date_created ? $date_created->format( 'Y-m-d' ) : date( 'Y-m-d', time() );
	}
	
	public function get_customer_account_updated_time_frame() {
		$date = $this->get_last_updated();
		
		return $date ? $date->format( 'Y-m-d' ) : date( 'Y-m-d', time() );
	}
	
	/**
	 * Get DateTime for first shipping address usage
	 *
	 * @since 1.7.0
	 *
	 * @param \WC_Order $order
	 *
	 * @return string
	 */
	public function get_shipping_address_first_use( $order ) {
		if ( 0 == $this->get_customer()->get_id() ) {
			return date( 'Y-m-d', time() );
		}
		
		$arguments = array(
			'customer'           => $this->get_customer()->get_id(),
			'limit'              => 1,
			'orderby'            => 'date',
			'order'              => 'ASC',
			'ids'                => true,
			'shipping_address_1' => $order->get_shipping_address_1(),
		);
		
		/** @var array $orders */
		$orders         = $this->get_orders( $arguments );
		$first_order_id = reset( $orders );
		
		/** @var \WC_Order $first_order */
		$first_order    = wc_get_order( $first_order_id );
		$first_use_date = new \DateTime();
		if ( $first_order ) {
			$first_use_date = $first_order->get_date_created();
		}
		
		return $first_use_date->format( 'Y-m-d' );
	}
	
	/**
	 * Get successful orders within last six months
	 * Successful order status includes:
	 *  - processing (purchase/debit) - standard WC Order State
	 *  - completed - standard WC Order State for shipped orders
	 *  - refunded - standard WC Order State for refunded orders (still successful)
	 *  - cancelled - standard WC Order State for cancelled order (still successful)
	 *
	 * @return int
	 * @since 1.7.0
	 */
	public function get_successful_orders_last_six_months() {
		
		if ( 0 == $this->get_customer()->get_id() ) {
			return 0;
		}
		
		$arguments = array(
			'customer'   => $this->get_customer()->get_id(),
			'limit'      => - 1,
			'ids'        => true,
			'status'     => apply_filters( 'wc_pay360_successful_order_statuses', array(
				'processing',
				'completed',
				'refunded',
				'cancelled',
			) ),
			'date_after' => '6 months ago',
		);
		$orders    = $this->get_orders( $arguments );
		
		return count( $orders );
	}
	
	public function get_orders_last_day() {
		if ( 0 == $this->get_customer()->get_id() ) {
			return 0;
		}
		
		$arguments = array(
			'customer'   => $this->get_customer()->get_id(),
			'limit'      => - 1,
			'ids'        => true,
			'date_after' => '1 day ago',
		);
		$orders    = $this->get_orders( $arguments );
		
		return count( $orders );
	}
	
	public function get_orders_last_year() {
		if ( 0 == $this->get_customer()->get_id() ) {
			return 0;
		}
		
		$arguments = array(
			'customer'   => $this->get_customer()->get_id(),
			'limit'      => - 1,
			'ids'        => true,
			'date_after' => '12 months ago',
		);
		$orders    = $this->get_orders( $arguments );
		
		return count( $orders );
	}
	
	/**
	 * Get array with wc order data according to arguments
	 * Override paginate = false to avoid stdClass
	 *
	 * @param $args
	 *
	 * @return array
	 * @since 1.7.0
	 */
	private function get_orders( $args ) {
		$no_paginate = array(
			'paginate' => false,
		);
		$args        = array_merge( $no_paginate, $args );
		$orders      = wc_get_orders( $args );
		
		return $orders;
	}
}