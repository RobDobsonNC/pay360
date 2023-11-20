<?php

namespace WcPay360\Updates;

class Tools_Actions {
	
	public function hooks() {
		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}
		
		add_filter( 'woocommerce_debug_tools', array( $this, 'add_tools' ) );
	}
	
	public function add_tools( array $tools ): array {
		
		$tools['pay360_update_subs_transaction_ids'] = array(
			'name'             => __( 'Pay360 Updates', \WC_Pay360::TEXT_DOMAIN ),
			'desc'             => __( 'Will run through all Pay360 active Subscriptions and attempt to get the initial transaction id.', \WC_Pay360::TEXT_DOMAIN ),
			'button'           => __( 'Update Pay360 Subscriptions', \WC_Pay360::TEXT_DOMAIN ),
			'callback'         => array( $this, 'update_pay360_subscriptions' ),
			'requires_refresh' => true,
		);
		
		return $tools;
	}
	
	public function update_pay360_subscriptions() {
		$this->security_check();

		$sub_updates = new Subscriptions_Update();
		$sub_updates->maybe_populate_subscriptions_transaction_id();

		return __( 'Transaction IDs for the Subscriptions were added.', \WC_Pay360::TEXT_DOMAIN );
	}
	
	private function security_check() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_die( esc_html__( 'You do not have permission to process this request.', \WC_Pay360::TEXT_DOMAIN ) );
		}
	}
}
