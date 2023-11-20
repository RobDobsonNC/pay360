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
class Updates_Manager {
	
	public function load() {
		$this->load_version_based_updates();
	}
	
	public function hooks() {
		add_action( 'admin_init', array( $this, 'load_tools' ), 10 );
	}
	
	public function load_tools() {
		$tools = new Tools_Actions();
		$tools->hooks();
	}
	
	public function load_version_based_updates() {
		$update = new Version_Based_Updates();
		$update->hooks();
	}
}