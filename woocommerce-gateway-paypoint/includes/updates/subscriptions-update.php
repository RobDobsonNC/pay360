<?php

namespace WcPay360\Updates;

use WcPay360\Pay360_Order;

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
class Subscriptions_Update {
	
	public function maybe_populate_subscriptions_transaction_id() {
		$subscriptions = wcs_get_subscriptions( array(
			'payment_method'      => 'pay360',
			'subscription_status' => array( 'active' ),
		) );
		
		foreach ( $subscriptions as $subscription ) {
			$pay360_sub = new Pay360_Order( $subscription );
			
			if ( $pay360_sub->get_initial_transaction_id() ) {
				continue;
			}
			
			if ( $subscription->get_meta( '_pay360_populate_init_trans_processed', true ) ) {
				continue;
			}
			
			$this->attempt_to_populate_initial_transaction( $subscription );
		}
	}
	
	/**
	 * @param \WC_Subscription $subscription
	 *
	 * @return bool|string The transaction ID or false
	 */
	public function attempt_to_populate_initial_transaction( $subscription ) {
		$pay360_sub = new Pay360_Order( $subscription );
		
		$resubscribe_orders = $subscription->get_related_orders( 'all', 'resubscribe' );
		
		if ( $resubscribe_orders ) {
			/**
			 * @var \WC_Order $resubscribe_order
			 */
			$resubscribe_order = array_pop( $resubscribe_orders );
			if ( 'pay360' == $resubscribe_order->get_payment_method() ) {
				$transaction_id = $resubscribe_order->get_transaction_id();
				
				// The last resubscribe will be the initial order for this subscription
				$pay360_sub->save_initial_transaction_id( $transaction_id );
				$subscription->add_meta_data( '_pay360_populate_init_trans_processed', true, true );
				$pay360_sub->save();
				
				return $transaction_id;
			}
		}
		
		/**
		 * @var \WC_Order[] $orders
		 */
		$parent_order = $subscription->get_related_orders( 'all', array( 'parent' ) );
		
		// Get the transaction if of the parent order
		if ( $parent_order ) {
			$parent_order = array_shift( $parent_order );
			if ( 'pay360' == $parent_order->get_payment_method() ) {
				$transaction_id = $parent_order->get_transaction_id();
				
				$pay360_sub->save_initial_transaction_id( $transaction_id );
				$subscription->add_meta_data( '_pay360_populate_init_trans_processed', true, true );
				$pay360_sub->save();
				
				return $transaction_id;
			}
		}
		
		$parent_order = $subscription->get_related_orders( 'all', array( 'renewal' ) );
		
		// Get the transaction ID for the first renewal
		foreach ( $orders as $order ) {
			if ( 'pay360' == $order->get_payment_method() ) {
				$transaction_id = $order->get_transaction_id();
				
				$pay360_sub->save_initial_transaction_id( $transaction_id );
				$subscription->add_meta_data( '_pay360_populate_init_trans_processed', true, true );
				$pay360_sub->save();
				
				return $transaction_id;
			}
		}
		
		return false;
	}
}