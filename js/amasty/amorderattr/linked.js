/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/

var amLinkedFields = new Class.create();

amLinkedFields.prototype = {
    initialize: function(parentField, childField, options, relations)
    {
        this.parentField = parentField;
        this.childField  = childField;
        this.options     = options;
        this.relations   = relations;
        if ($(this.parentField))
        {
            $(this.parentField).observe('change', this.onChange.bind(this));
        }
        this.onChange();
    },
    
    onChange: function()
    {
        $(this.childField).childElements().each(function(elem){ elem.remove(); });
        this.options.each(function(option){
            if (this.relations[option.value] == $(this.parentField).value || "" == option.value)
            {
                optionElement = document.createElement('option');
                optionElement.value     = option.value;
                optionElement.text      = option.label;
                optionElement.innerText = option.label;
                $(this.childField).appendChild(optionElement);
            }
        }.bind(this));
    }
};