( function( $ ) {
// placeholder for javascript
gform.addFilter(
  'gform_product_total',
  function( total, formId ) {
    var recoverfeesDom = document.querySelector('#gform_wrapper_' + formId + ' .ginput_recover_fees_input');
    var recoverfeesCheckBox = document.querySelector('#gform_wrapper_' + formId + ' .recoverfeesCheck');

    if(!recoverfeesDom) return total;
    var productsIds = recoverfeesDom.getAttribute("data-products");
    productsIds = JSON.parse(productsIds);
    var recoverfeesTotal = 0;
    var recoverfeesAmount = recoverfeesDom.getAttribute("data-amount-percent");
    var recoverfeesDollars = recoverfeesDom.getAttribute("data-amount-dollars");
    if(!recoverfeesDollars) recoverfeesDollars = 0;

    var productType = recoverfeesDom.getAttribute("data-productstype");
    if(productType == 'all') {
      recoverfeesTotal = total * recoverfeesAmount / 100 + Number(recoverfeesDollars);
    } else {
      for(var id of productsIds) {
        if(gformCalculateProductPrice( formId, id ) > 0) {
          recoverfeesTotal += gformCalculateProductPrice( formId, id ) * recoverfeesAmount / 100 + Number(recoverfeesDollars);
        }
        
      }
    }
    
    recoverfeesDom.value = recoverfeesTotal;
    if(total == 0) recoverfeesDom.value = 0;
    var event = new Event('change');
    recoverfeesDom.dispatchEvent(event);
    // var productTotal = gformCalculateProductPrice( formId, productIds[i] );
		// 		total           += productTotal;
    recoverfeesCheckBox.onchange = function(){
      gformCalculateTotalPrice(formId);
    };

    if(recoverfeesCheckBox.checked && total >  0) {
      return total + recoverfeesTotal;
    } else {
      return total;
    }
    
  }, 52
);


} )( jQuery );
