switchTemplates = function(value) {
    if (typeof this.allElements == 'undefined' || $$('select.template_selector').length) {
        this.allElements = this.allElements || {};

        var self = this;
        $$('select.template_selector').each(function(el) {
                var pn = self.rowContainer = el.parentNode.parentNode.parentNode;
                var rc = el.parentNode.parentNode;
                self.allElements[el.id] = (pn.removeChild(rc));
            }
        )
    }

    var postfix = value + ($F('recipient') == 'customer' ? '' : '_admin');

    if (typeof this.allElements['email_template_' + postfix] != 'undefined') {
        this.rowContainer.appendChild(this.allElements['email_template_' + postfix])
    }
}
$(document).observe('dom:loaded', function() {
    var values = {};
    $$('select.template_selector').each(function(el) {
        values[el.id] = el.getValue()
    });
    switchTemplates($('type').getValue());
    $$('select.template_selector').each(function(el) {
        el.setValue(values[el.id])
    })
});;
