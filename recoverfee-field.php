<?php 
namespace recoverFees;
class RecoverFees_Field extends \GF_Field {
	public $type = 'recover_fees';
	protected $_slug = 'recover_fees_slug';
	private $_processing_order = false;
  public function run() {
		add_action( 'gform_field_standard_settings_100', array( $this, 'field_settings_ui' ) );
		add_action( 'gform_editor_js', array( $this, 'field_settings_js' ) );
		add_action( 'gform_product_info', array( $this, 'add_recoverfees_to_order' ), 9, 3 );
		// add_action( 'admin_enqueue_scripts', array( $this, 'recoverfees_script_load')); // wp_enqueue_scripts
		// wp_register_script( 'gwp-admin', plugin_dir_url( __FILE__ ) . 'js/custom-scripts.js' );

		\GF_Fields::register( new \recoverFees\RecoverFees_Field() );

		
	}

	public function get_form_editor_field_title() {
		return esc_attr__( 'Recover Fees', 'AAAAAAAA' );
	}

	public function get_form_editor_button() {
		return array(
			'group' => 'pricing_fields',
			'text'  => 'Recover Fees',
		);
	}

	public function get_field_input( $form, $value = '', $entry = null ) {
		$form_id         = $form['id'];
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		$id      = (int) $this->id;
		$html_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

		if ( $is_entry_detail ) {
			return ''; // field should not be displayed on entry detail
		} else {
			return $this->get_input_markup( $form_id, $id, $html_id );
		}
	}


	public function get_input_markup( $form_id, $field_id, $html_id ) {
		return "
					<div class='ginput_container'>
							<input type='checkbox' class='recoverfeesCheck' name='recoverFeeCheck' value='Bike'>
							<label for='recoverFeeCheck'> Help cover our transaction fees <span>$$$</span> so that 100% of your donation goes to those in need.</label>
							<input type='hidden' name='input_{$field_id}' id='{$html_id}' class='gform_hidden ginput_{$this->type}_input' 
									onchange='jQuery( this ).prev().find(\"span\").text( gformFormatMoney( this.value, true ));' 
									data-amount-percent='{$this->{$this->type . 'AmountPercent'}}' data-amount-dollars='{$this->{$this->type . 'AmountDollars'}}'
									data-productstype='{$this->{$this->type . 'ProductsType'}}' data-products='" . json_encode( $this->{$this->type . 'Products'} ) . "' />
					</div>";
	}

	public function get_form_editor_field_settings() {
		return array(
			'label_setting',
			// 'description_setting',
			'css_class_setting',
			'admin_label_setting',
			'label_placement_setting',
			'conditional_logic_field_setting',
			'recoverfees-amount-setting',
			'recoverfees-products-setting',
		);
	}

	public function field_settings_ui() {
		?>
		<li class="recoverfees-amount-setting field_setting gp-field-setting" >
			<label for="recoverfees-amount" class="section_label">
				<span class="tax-label recoverfees-label">
					<?php _e( 'Recover Fees Amount', 'recover-fees' ); ?>
				</span>
			</label>
			<input type="text" id="recoverfees-amount-percent" size="10" onblur="RecoverFeesFormEditor.parseAmount( this.value, this, 'percent');" />
			<input type="text" id="recoverfees-amount-cents" size="10" onblur="RecoverFeesFormEditor.parseAmount( this.value, this, 'dollars');" />
		</li>

		<li class="recoverfees-products-setting field_setting gp-field-setting" >
			<label for="recoverfees-products-type" class="section_label">
				<?php _e( 'Applicable Products', 'recover-fees' ); ?>
			</label>
			<div class="gpecf-products-type-setting gp-group">
				<span class="tax-label recoverfees-label inline-select-label">
					<?php _e( 'Apply recover fees to', 'recover-fees' ); ?>
				</span>
				<select id="recoverfees-products-type" onchange="RecoverFeesFormEditor.toggleProductsType( this.value, this );">
					<option value="all"><?php _e( 'all products', 'recover-fees' ); ?></option>
					<option value="include"><?php _e( 'specific products', 'recover-fees' ); ?></option>
					<option value="exclude"><?php _e( 'all products with exceptions' ); ?></option>
				</select>
			</div>

			<div id="recoverfees-products-settings" class="perk-settings-container gpecf-child-setting" style="display:none;">
				<select id="recoverfees-products" multiple="multiple" title="<?php _e( 'Select Products', 'recover-fees' ); ?>">
					<option value=""><?php _e( 'Select Products', 'recover-fees' ); ?></option>
				</select>
			</div>

		</li>
		<?php
	}
	public function field_settings_js() {
		?>
		<script type="text/javascript">
			var RecoverFeesFormEditor;
			( function( $ ) {
				RecoverFeesFormEditor = {

					parseAmount: function( amount, elem, amountType ) {

						if( typeof amount != 'string' ) {
							amount = String( amount );
						}

						var type            = GetSelectedField().type,
							isPercentage    = type == 'recover_fees' || amount.indexOf( '%' ) != -1 || amountType == 'percent',
							isPercentage    = amountType != 'dollars'
							amount          = Math.abs( gformToNumber( amount ) ),
							parsedAmount    = amount != false ? amount : 0,
							parsedAmount    = isPercentage ? Math.min( amount, 100 ) : amount;
							formattedAmount = isPercentage ? gformFormatNumber( parsedAmount, -1 ) + '%' : gformFormatMoney( parsedAmount, true ),
							$input          = $( elem );

						// save "clean" number
						console.log("type=====>", type);
						console.log("isPercentage=====>", isPercentage);
						if(amountType == "percent") {
							SetFieldProperty( type + 'AmountPercent', parsedAmount );
						} else if(amountType == "dollars") {
							SetFieldProperty( type + 'AmountDollars', parsedAmount );
						}
						// display formatted number based on default currency
						$input.val( formattedAmount );

					},
					
					toggleProductsType: function( value, elem, isInit ) {

						var type             = GetSelectedField().type,
							$productsType    = $( elem ),
							value            = ! value ? 'all' : value,
							$childSettings   = $( '#recoverfees-products-settings' ),
							isApplicableType = $.inArray( value, [ 'include', 'exclude' ] ) != -1,
							isInit           = typeof isInit != 'undefined' ? isInit : false;

						SetFieldProperty( type + 'ProductsType', value );

						$productsType.val( value );

						if( ! isInit ) {
							var $products = $( '#recoverfees-products' );
							$products.val( false ).change();
						}

						if( isApplicableType ) {
							$childSettings.show();
						} else {
							$childSettings.hide();
						}

					},

					populateProducts: function( form, products ) {

						var fields    = RecoverFeesFormEditor.getProductFields( form ),
							markup    = '',
							$products = $( '#recoverfees-products' ),
							products  = products ? products : [];

						for( var i = 0; i < fields.length; i++ ) {
							var selected = $.inArray( String( fields[ i ].id ), products ) != -1 ? 'selected="selected"' : '';
							markup += '<option value="' + fields[ i ].id + '" ' + selected + '>' + GetLabel( fields[ i ] ) + '</option>'
						}

						$products.html( markup ).change();

						if( ! $products.data( 'asmApplied' ) ) {
							$products.asmSelect().data( 'asmApplied', true );
						}

					},

					getProductFields: function( form ) {

						var productFields = [];

						for( var i = 0; i < form.fields.length; i++ ) {
							if( form.fields[ i ].type == 'product' ) {
								productFields.push( form.fields[ i ] );
							}
						}

						return productFields;
					},

					setProducts: function( products ) {
						var type = GetSelectedField().type;
						SetFieldProperty( type + 'Products', products );
					},

					toggleLabels: function( type ) {
						$( '.recoverfees-label' ).hide();
						$( '.{0}-label'.format( type ) ).show();
					}

				};
				$( document ).bind( 'gform_load_field_settings', function( event, field, form ) {
					if($.inArray( field.type, [ 'recover_fees' ] ) != -1 ) {

						RecoverFeesFormEditor.parseAmount( field[ field.type + 'AmountPercent' ], $( '#recoverfees-amount-percent' ), 'percent');
						RecoverFeesFormEditor.parseAmount( field[ field.type + 'AmountDollars' ], $( '#recoverfees-amount-cents' ), 'dollars');
						RecoverFeesFormEditor.toggleProductsType( field[ field.type + 'ProductsType' ], $( '#recoverfees-products-type' ), true );
						RecoverFeesFormEditor.populateProducts( form, field[ field.type + 'Products' ] );
						// RecoverFeesFormEditor.toggleLabels( field.type );

						// administrative should not be a visibility option for recoverfees fields
						$( '#field_visibility_administrative, label[for="field_visibility_administrative"]' ).attr( 'style', 'display: none !important;' );

					} else {

				// administrative should not be a visibility option for ecommerce fields
				$( '#field_visibility_administrative, label[for="field_visibility_administrative"]' ).attr( 'style', '' );

				}

					} );

					$( document ).ready( function() {

					$( '#recoverfees-products' ).change( function() {
						RecoverFeesFormEditor.setProducts( $( this ).val() );
					} );

					} );
				
			})( jQuery );
		</script>
		<?php
	}

	public static function recoverfees_script_load() {
		wp_enqueue_script( 'my-custom-script', plugin_dir_url( __FILE__ ) . 'js/custom-scripts.js' );
	}
	
}
