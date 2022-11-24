<?php
/*
Plugin Name: Gravity Forms Recover Fees
Plugin URI: http://www.gravityforms.com
Description: A Recover Fees to demonstrate the use of the Add-On Framework
Version: 1.0
Author: Jhonatan S.
Author URI: http://www.jhontatantopdev.com

------------------------------------------------------------------------
*/

if ( ! class_exists( 'GFAddOn' ) ) {
	return;
}

define( 'GF_SIMPLE_ADDON_VERSION', '2.1' );
use recoverFees\RecoverFees_Field;

add_action( 'gform_loaded', array( 'GF_RecoverFees_Bootstrap', 'load' ), 5 );

class GF_RecoverFees_Bootstrap { 

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( 'recoverfee-addon.php' );
        
        GFAddOn::register( 'GFRecoverFeesAddOn' );

        require_once( 'recoverfee-field.php' );
        $recoverFees_field      = new RecoverFees_Field();
        $recoverFees_field -> run(gf_simple_addon());
    }

}

function gf_simple_addon() {
    return GFRecoverFeesAddOn::get_instance();
}
