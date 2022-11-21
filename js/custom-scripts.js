( function( $ ) {
// placeholder for javascript
gform.addFilter(
  'gform_product_total',
  function( total, formId ) {
    console.log("total=====>", total);
    var recoverfeesDom = document.querySelector('#gform_wrapper_' + formId + ' .ginput_recover_fees_input');
    var recoverfeesCheckBox = document.querySelector('#gform_wrapper_' + formId + ' .recoverfeesCheck');

    if(!recoverfeesDom) return total;
    var productsIds = recoverfeesDom.getAttribute("data-products");
    productsIds = JSON.parse(productsIds);
    var recoverfeesTotal = 0;
    var recoverfeesAmount = recoverfeesDom.getAttribute("data-amount");
    var productType = recoverfeesDom.getAttribute("data-productstype");
    if(productType == 'all') {
      recoverfeesTotal = total * recoverfeesAmount / 100;
    } else {
      for(var id of productsIds) {
        recoverfeesTotal += gformCalculateProductPrice( formId, id ) * recoverfeesAmount / 100;
      }
    }
    
    recoverfeesDom.value = recoverfeesTotal;

    var event = new Event('change');
    recoverfeesDom.dispatchEvent(event);
    // var productTotal = gformCalculateProductPrice( formId, productIds[i] );
		// 		total           += productTotal;
    recoverfeesCheckBox.onchange = function(){gformCalculateTotalPrice(formId);};

    if(recoverfeesCheckBox.checked) {
      return total + recoverfeesTotal;
    } else {
      return total;
    }
    
  }, 52
);

} )( jQuery );
