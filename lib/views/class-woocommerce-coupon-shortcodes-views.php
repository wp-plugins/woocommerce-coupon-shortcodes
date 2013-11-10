<?php
/**
 * class-woocommerce-coupon-shortcodes-views.php
 *
 * Copyright (c) 2013 "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 * 
 * @author Karim Rahimpur
 * @package woocommerce-coupon-shortcodes
 * @since woocommerce-coupon-shortcodes 1.0.0
 */

/**
 * Shortcodes.
 */
class WooCommerce_Coupon_Shortcodes_Views {

	/**
	 * Adds shortcodes.
	 */
	public static function init() {
		add_shortcode( 'coupon_is_applied', array( __CLASS__, 'coupon_is_applied' ) );
		add_shortcode( 'coupon_is_valid', array( __CLASS__, 'coupon_is_valid' ) );
		add_shortcode( 'coupon_is_not_valid', array( __CLASS__, 'coupon_is_not_valid' ) );
		add_shortcode( 'coupon_code', array( __CLASS__, 'coupon_code' ) );
		add_shortcode( 'coupon_description', array( __CLASS__, 'coupon_description' ) );
		add_shortcode( 'coupon_discount', array( __CLASS__, 'coupon_discount' ) );
	}

	/**
	 * Evaluate coupons applied based on op and coupon codes.
	 *
	 * @param array $atts
	 * @return boolean
	 */
	private static function _is_applied( $atts ) {

		global $woocommerce_coupon_shortcodes_codes;

		$options = shortcode_atts(
			array(
				'coupon' => null,
				'code'   => null,
				'op'     => 'and'
			),
			$atts
		);

		$code = null;
		if ( !empty( $options['code'] ) ) {
			$code = $options['code'];
		} else if ( !empty( $options['coupon'] ) ) {
			$code = $options['coupon'];
		}
		if ( $code === null ) {
			return '';
		}

		$applied_coupon_codes = self::_get_applied_codes();

		$codes = array_map( 'trim', explode( ',', $code ) );
		if ( !in_array( '*', $codes ) ) {
			$woocommerce_coupon_shortcodes_codes = $codes;
			$applied = array();
			foreach ( $codes as $code ) {
				$applied[] = in_array( $code, $applied_coupon_codes );
			}
			switch( strtolower( $options['op'] ) ) {
				case 'and' :
					$is_applied = self::conj( $applied );
					break;
				default :
					$is_applied = self::disj( $applied );
			}
		} else {
			$woocommerce_coupon_shortcodes_codes = $applied_coupon_codes;
			$is_applied = !empty( $applied_coupon_codes );
		}
		return $is_applied;
	}

	/**
	 * Returns the valid coupon codes currently applied to the cart.
	 * 
	 * @return array of string with coupon codes
	 */
	private static function _get_applied_codes() {
		global $woocommerce;
		$applied_coupon_codes = array();
		if ( isset( $woocommerce ) && isset( $woocommerce->cart ) ) {
			$cart = $woocommerce->cart;
			if ( ! empty( $cart->applied_coupons ) ) {
				foreach ( $cart->applied_coupons as $key => $code ) {
					$coupon = new WC_Coupon( $code );
					if ( ! is_wp_error( $coupon->is_valid() ) ) {
						$applied_coupon_codes[] = $code;
					}
				}
			}
		}
		return $applied_coupon_codes;
	}

	/**
	 * Evaluate common validity based on op and coupon codes.
	 * 
	 * @param array $atts
	 * @return boolean
	 */
	private static function _is_valid( $atts ) {

		global $woocommerce_coupon_shortcodes_codes;

		$options = shortcode_atts(
			array(
				'coupon' => null,
				'code'   => null,
				'op'     => 'and'
			),
			$atts
		);

		$code = null;
		if ( !empty( $options['code'] ) ) {
			$code = $options['code'];
		} else if ( !empty( $options['coupon'] ) ) {
			$code = $options['coupon'];
		}
		if ( $code === null ) {
			return '';
		}

		$codes = array_map( 'trim', explode( ',', $code ) );
		$woocommerce_coupon_shortcodes_codes = $codes;

		$validities = array();
		foreach ( $codes as $code ) {
			$coupon = new WC_Coupon( $code );
			if ( $coupon->id ) {
				$validities[] = $coupon->is_valid();
			}
		}

		switch( strtolower( $options['op'] ) ) {
			case 'and' :
				$valid = self::conj( $validities );
				break;
			default :
				$valid = self::disj( $validities );
		}

		return $valid;
	}

	/**
	 * Boolean AND on array elements.
	 * 
	 * @param array $a
	 * @return boolean true if all elements are true and there is at least one in the array, false otherwise
	 */
	public static function conj( $a ) {
		$r = false;
		if ( is_array( $a ) ) {
			$c = count( $a );
			if ( $c > 0 ) {
				$r = true;
				$i = 0;
				while( $r && ( $i < $c ) ) {
					$r = $r && $a[$i];
					$i++;
				}
			}
		}
		return $r;
	}

	/**
	 * Boolean OR on array elements.
	 * 
	 * @param array $a
	 * @return boolean true if at least one true element is in the array, false otherwise
	 */
	public static function disj( $a ) {
		$r = false;
		if ( is_array( $a ) ) {
			$c = count( $a );
			if ( $c > 0 ) {
				$r = false;
				$i = 0;
				while( !$r && ( $i < $c ) ) {
					$r = $r || $a[$i];
					$i++;
				}
			}
		}
		return $r;
	}

	/**
	 * Conditionally render content based on coupons which are applied.
	 *
	 * Takes a comma-separated list of coupon codes as coupon or code attribute.
	 *
	 * The op attribute determines whether all codes must be applied (and) or
	 * any code can be applied (or) for the content to be rendered.
	 *
	 * @param array $atts attributes
	 * @param string $content content to render
	 * @return string
	 */
	public static function coupon_is_applied( $atts, $content = null ) {
		$output = '';
		if ( !empty( $content ) ) {
			$applied = self::_is_applied( $atts );
			if ( $applied ) {
				remove_shortcode( 'coupon_is_applied' );
				$content = do_shortcode( $content );
				add_shortcode( 'coupon_is_applied', array( __CLASS__, 'coupon_is_applied' ) );
				$output = $content;
			}
		}
		return $output;
	}

	/**
	 * Conditionally render content based on coupon validity.
	 * 
	 * Takes a comma-separated list of coupon codes as coupon or code attribute.
	 * 
	 * The op attribute determines whether all codes must be valid (and) or
	 * any code can be valid (or) for the content to be rendered.
	 * 
	 * @param array $atts attributes
	 * @param string $content content to render
	 * @return string
	 */
	public static function coupon_is_valid( $atts, $content = null ) {
		$output = '';
		if ( !empty( $content ) ) {
			$valid = self::_is_valid( $atts );
			if ( $valid ) {
				remove_shortcode( 'coupon_is_valid' );
				$content = do_shortcode( $content );
				add_shortcode( 'coupon_is_valid', array( __CLASS__, 'coupon_is_valid' ) );
				$output = $content;
			}
		}
		return $output;
	}

	/**
	 * Conditionally render content based on coupon non-validity.
	 *
	 * Takes a comma-separated list of coupon codes as coupon or code attribute.
	 *
	 * The op attribute determines whether all codes must be valid (and) or
	 * any code can be valid (or) for the content to be rendered.
	 *
	 * @param array $atts attributes
	 * @param string $content content to render
	 * @return string
	 */
	public static function coupon_is_not_valid( $atts, $content = null ) {
		$output = '';
		if ( !empty( $content ) ) {
			$valid = !self::_is_valid( $atts );
			if ( $valid ) {
				remove_shortcode( 'coupon_is_not_valid' );
				$content = do_shortcode( $content );
				add_shortcode( 'coupon_is_not_valid', array( __CLASS__, 'coupon_is_not_valid' ) );
				$output = $content;
			}
		}
		return $output;
	}

	/**
	 * Returns an array of (potential) coupon codes obtained
	 * through the options or through the global that might have been
	 * set in _is_valid.
	 * 
	 * @param array $options
	 * @return array
	 */
	private static function get_codes( $options ) {
		global $woocommerce_coupon_shortcodes_codes;
		$codes = array();
		$code = null;
		if ( !empty( $options['code'] ) ) {
			$code = $options['code'];
		} else if ( !empty( $options['coupon'] ) ) {
			$code = $options['coupon'];
		}
		if ( $code === null ) {
			if ( !empty( $woocommerce_coupon_shortcodes_codes ) ) {
				$codes = $woocommerce_coupon_shortcodes_codes;
			} else {
				return '';
			}
		}
		if ( empty( $codes ) ) {
			$codes = array_map( 'trim', explode( ',', $code ) );
		}
		return $codes;
	}

	/**
	 * Renders the code(s) of coupon(s).
	 *
	 * @param array $atts
	 * @param string $content not used
	 * @return string
	 */
	public static function coupon_code( $atts, $content = null ) {

		$output = '';
		$options = shortcode_atts(
			array(
				'coupon'    => null,
				'code'      => null,
				'separator' => ' '
			),
			$atts
		);

		$codes = self::get_codes( $options );
		foreach ( $codes as $code ) {
			$coupon = new WC_Coupon( $code );
			if ( $coupon->id ) {
				$output .= sprintf( '<span class="coupon code %s">', stripslashes( wp_strip_all_tags( $coupon->code ) ) );
				$output .= stripslashes( wp_strip_all_tags( $coupon->code ) );
				$output .= '</span>';
				$output .= stripslashes( wp_filter_kses( $options['separator'] ) );
			}
		}
		return $output;
	}

	/**
	 * Renders the description(s) of coupon(s).
	 * 
	 * @param array $atts
	 * @param string $content not used
	 * @return string
	 */
	public static function coupon_description( $atts, $content = null ) {
		$output = '';
		$options = shortcode_atts(
			array(
				'coupon'      => null,
				'code'        => null,
				'separator'   => ' ',
				'element_tag' => 'span'
			),
			$atts
		);

		switch( $options['element_tag'] ) {
			case 'li' :
			case 'span' :
			case 'div' :
			case 'p' :
				$element_tag = $options['element_tag'];
				break;
			default :
				$element_tag = 'span';
		}

		$elements = array();
		$codes = self::get_codes( $options );
		foreach ( $codes as $code ) {
			$coupon = new WC_Coupon( $code );
			if ( $coupon->id ) {
				if ( $post = get_post( $coupon->id ) ) {
					if ( !empty( $post->post_excerpt ) ) {
						$elements[] =
							sprintf( '<%s class="coupon description %s">', stripslashes( wp_strip_all_tags( $element_tag ) ), stripslashes( wp_strip_all_tags( $coupon->code ) ) ) .
							stripslashes( wp_filter_kses( $post->post_excerpt ) ) .
							sprintf( '</%s>', stripslashes( wp_strip_all_tags( $element_tag ) ) );
					}
				}
			}
		}

		if ( $element_tag == 'li' ) {
			$output .= '<ul>';
		}
		$output .= implode( stripslashes( wp_filter_kses( $options['separator'] ) ), $elements );
		if ( $element_tag == 'li' ) {
			$output .= '</ul>';
		}

		return $output;
	}

	/**
	 * Renders information about the discount for coupon(s).
	 *
	 * @param array $atts
	 * @param string $content not used
	 * @return string
	 */
	public static function coupon_discount( $atts, $content = null ) {
		$output = '';
		$options = shortcode_atts(
			array(
				'coupon'      => null,
				'code'        => null,
				'separator'   => ' ',
				'element_tag' => 'span',
				'renderer'    => 'auto'
			),
			$atts
		);

		switch( $options['element_tag'] ) {
			case 'li' :
			case 'span' :
			case 'div' :
			case 'p' :
				$element_tag = $options['element_tag'];
				break;
			default :
				$element_tag = 'span';
		}

		$elements = array();
		$codes = self::get_codes( $options );
		foreach ( $codes as $code ) {
			$element_output = '';
			$coupon = new WC_Coupon( $code );
			if ( $coupon->id ) {
				$element_output .= sprintf( '<%s class="coupon discount %s">', stripslashes( wp_strip_all_tags( $element_tag ) ), stripslashes( wp_strip_all_tags( $coupon->code ) ) );

				$renderer = null;
				if ( $options['renderer'] == 'auto' ) {

					// WooCommerce_Coupons_Countdown_Shortcodes
					// does not differ

					// WooCommerce_Groupons_Shortcodes
					// does not differ

					// WooCommerce_Volume_Discount_Coupons_Shortcodes
					if ( class_exists( 'WooCommerce_Volume_Discount_Coupons_Shortcodes' ) ) {
						$min = get_post_meta( $coupon->id, '_vd_min', true );
						$max = get_post_meta( $coupon->id, '_vd_max', true );
						if ( ( $min > 0 ) || ( $max > 0 ) ) {
							$renderer = 'WooCommerce_Volume_Discount_Coupons_Shortcodes';
						}
					}

				}

				if ( $renderer === null ) {
					$element_output .= self::get_discount_info( $coupon, $atts );
				} else {
					switch( $renderer ) {
						case 'WooCommerce_Volume_Discount_Coupons_Shortcodes' :
							$element_output .= WooCommerce_Volume_Discount_Coupons_Shortcodes::get_volume_discount_info( $coupon );
							break;
					}
				}
				$element_output .= sprintf( '</%s>', stripslashes( wp_strip_all_tags( $element_tag ) ) );
			}
			if ( !empty( $element_output ) ) {
				$elements[] = $element_output;
			}
		}
		if ( $element_tag == 'li' ) {
			$output .= '<ul>';
		}
		$output .= implode( stripslashes( wp_filter_kses( $options['separator'] ) ), $elements );
		if ( $element_tag == 'li' ) {
			$output .= '</ul>';
		}
		return $output;
	}

	/**
	 * Returns a description of the discount.
	 *
	 * @param WC_Coupon $coupon
	 * @return string HTML describing the discount
	 */
	public static function get_discount_info( $coupon, $atts = array() ) {
		$product_delimiter = isset( $atts['product_delimiter'] ) ? $atts['product_delimiter'] : ', ';
		$category_delimiter = isset( $atts['category_delimiter'] ) ? $atts['category_delimiter'] : ', ';
		$result = '';

		$amount_suffix = get_woocommerce_currency_symbol();
		switch( $coupon->type ) {
			case 'percent' :
			case 'percent_product' :
				$amount_suffix = '%';
				break;
		}

		$products = array();
		$categories = array();
		switch ( $coupon->type ) {
			case 'fixed_product' :
			case 'percent_product' :
				if ( sizeof( $coupon->product_ids ) > 0 ) {
					foreach( $coupon->product_ids as $product_id ) {
						$product = get_product( $product_id );
						if ( $product ) {
							$products[] = sprintf(
								'<span class="product-link"><a href="%s">%s</a></span>',
								esc_url( get_permalink( $product_id ) ),
								$product->get_title()
							);
						}
					}
				}
				if ( sizeof( $coupon->product_categories ) > 0 ) {
					foreach( $coupon->product_categories as $term_id ) {
						if ( $term = get_term_by( 'id', $term_id, 'product_cat' ) ) {
							$categories[] = sprintf(
								'<span class="product-link"><a href="%s">%s</a></span>',
								get_term_link( $term->slug, 'product_cat' ),
								esc_html( $term->name )
							);
						}
					}
				}
				break;
		}

		switch ( $coupon->type ) {
			case 'fixed_product' :
			case 'percent_product' :
				if ( sizeof( $coupon->product_ids ) > 0 ) {
					if ( count( $products ) > 0 ) {
						$result = sprintf( __( '%s%s Discount on %s', WOO_CODES_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix, implode( $product_delimiter, $products ) );
					} else {
						$result = sprintf( __( '%s%s Discount on selected products', WOO_CODES_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix );
					}
				} else if ( sizeof( $coupon->product_categories ) > 0 ) {
					$result = sprintf( __( '%s%s Discount in %s', WOO_CODES_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix, implode( $category_delimiter, $categories ) );
				} else if ( sizeof( $coupons->exclude_product_ids ) > 0 || sizeof( $coupon->exclude_product_categories ) > 0 ) {
					$result = sprintf( __( '%s%s Discount on selected products', WOO_CODES_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix );
				} else {
					$result = sprintf( __( '%s%s Discount', WOO_CODES_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix );
				}

				break;
			case 'fixed_cart' :
			case 'percent' :
				$result = sprintf( __( '%s%s Discount', WOO_CODES_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix );
				break;
		}

		return apply_filters( 'woocommerce_coupon_shortcodes_info', $result, $coupon );
	}

}
WooCommerce_Coupon_Shortcodes_Views::init();
