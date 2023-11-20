<?php
/**
 * Iframe HTML template.
 *
 * Don't edit this template directly as it will be overwritten with every plugin update.
 * Override this template by copying it to yourtheme/woocommerce/pay360/iframe.php
 *
 * @since  3.0
 * @author VanboDevelops
 */
?>
<iframe
	name="pay360-iframe"
	id="pay360-iframe"
	src="<?php echo esc_url( $location ); ?>"
	frameborder="0"
	width="<?php echo esc_attr( $width ); ?>"
	height="<?php echo esc_attr( $height ); ?>"
	scrolling="<?php echo esc_attr( $scroll ); ?>"
>
	<p>
		<?php echo \WcPay360\Helpers\Formatting::kses_form_html( sprintf(
			__(
				'Your browser does not support iframes.
			 %sClick Here%s to get redirected to Pay360 payment page. ', \WC_Pay360::TEXT_DOMAIN
			),
			'<a href="' . esc_url( $location ) . '">',
			'</a>'
		) ); ?>
	</p>
</iframe>
