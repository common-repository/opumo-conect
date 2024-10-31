<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Opumo_Pixel_PX {

	/**
	* @var WC_Order $order WooCommere order object https://docs.woothemes.com/wc-apidocs/class-WC_Order.html
	* @var string $version Plugin version
	*/

	private $order, $version;

	public function __construct( $version ) {
		$this->version = $version;

		return $this;
	}

	public function woocommerce_thankyou( $order_id ) {

		$options        = get_option( 'opumo_connect_options' );
		$merchant_id    = @$options['merchant-id'];
		
		if ( ! $order_id || ! $merchant_id ) {
			echo '<!-- no OPUMO Connect merchant ID entered or order ID doesn\'t exist-->';
			return;
		}
		
		$this->order = new WC_Order( $order_id );

		$js = 'var _paq = _paq || []; ';

		$items = $this->order->get_items();

		foreach($items as $item) {
			/** @var \WC_Order_Item $item */
			$product   = 0 != $item['variation_id'] ? new WC_Product_Variation($item['variation_id']) : new WC_Product($item['product_id']);
			$sku = $product->get_sku();
			$price = round(($item['line_total'] / $item['qty']), 2);
			$name = $item->get_name();

			$js .= '_paq.push([';
				$js .= '"addEcommerceItem",';
				$js .= '"'.$sku.'",'; // (required) SKU: Product unique identifier
				$js .= '"'.$name.'",'; // (optional) Product name
				$js .= 'null,'; // (optional) Product category. You can also specify an array of up to 5 categories
				$js .= $price .','; // (recommended) Product price
				$js .= $item['qty'];// (optional, default to 1) Product quantity
			$js .= ']); ';
		}

		$js .= '_paq.push(["trackEcommerceOrder", "' . $this->order->get_order_number() . '", ' . $this->get_order_amount() . ', null, ' . $this->order->get_total_tax() . ', ' . $this->get_total_shipping(false) . ']); ';
		$js .= '_paq.push(["trackEvent", "Sale", "' . $this->get_currency() . '", "' . $this->order->get_order_number() . '", ' . $this->get_order_amount() . ']); ';

		echo '<script>' . $js . '</script>';
	}

	private function get_order_amount() {

		$grand_total    = $this->order->get_total();
		$total_shipping = $this->get_total_shipping();
		$subtotal       = $grand_total - ( $total_shipping  );

		if ( $subtotal < 0 ) {
			$subtotal = 0;
		}

		return $subtotal;
	}

	private function get_total_shipping( $incl_tax = true)
	{
		$total_shipping = version_compare(WC()->version, '3.0') >= 0 ? $this->order->get_shipping_total() : $this->order->get_total_shipping();
		if($incl_tax) {
			$total_shipping += $this->order->get_shipping_tax();
		}
		return $total_shipping;
	}

	private function get_currency() {

		$currency = version_compare( WC()->version, '3.0' ) >= 0 ? $this->order->get_currency() : $this->order->get_order_currency();

		return $currency;
	}


	public function opumo_analytics_track_pageviews()
	{
		$options        = get_option('opumo_connect_options');
		$merchant_id    = @$options['merchant-id'];
		echo '<script type="text/javascript">';
			echo 'var _paq = window._paq = window._paq || [];';
			echo '_paq.push(["trackPageView"]);';
			echo '_paq.push(["enableLinkTracking"]);';
			echo '_paq.push(["enableHeartBeatTimer"]);';
			echo '(function () {';
				echo 'var u = "'. OPUMO_PIXEL_BASE.'";';
				echo '_paq.push(["setTrackerUrl", u + "oa.php"]);';
				echo '_paq.push(["setSiteId", "'. $merchant_id .'"]);';
				echo 'var d = document,';
					echo 'g = d.createElement("script"),';
					echo 's = d.getElementsByTagName("script")[0];';
				echo 'g.type = "text/javascript";';
				echo 'g.async = true;';
				echo 'g.src = u + "oa.js";';
				echo 's.parentNode.insertBefore(g, s);';
			echo '})();';
		echo '</script>';
		echo '<noscript>';
			echo '<p><img src="' . OPUMO_PIXEL_BASE . 'oa.php?idsite=' . $merchant_id . '&amp;rec=1" style="border:0;" alt="" /></p>';
		echo '</noscript>';
	}
}
