/**
 * This file is part of Choqled PHP Framework and/or part of a BFLDEV Software Product.
 * This file is licensed under "GNU General Public License" Version 3 (GPL v3).
 * If you find a bug or you want to contribute some code snippets, let me know at http://bfldev.com/nreeda
 * Suggestions and ideas are also always helpful.

 * @author Roland Eigelsreiter (BrainFooLong)
 * @product nReeda - Web-based Open Source RSS/XML/Atom Feed Reader
 * @link http://bfldev.com/nreeda
**/
/**
* Scripts for form module
*/
if(!jQuery) alert("jQuery is required for formtable scripts");

/**
* The formtable script
*
* @param container
*/
function FormTable(container){
    var self = this;
    this.container = container;
    this.container.data("formtable", self);
    this.validateAllFields = function(){
        var result = true;
        this.container.find("input, textarea, select").each(function(){
            if($(this).attr("name") == "submit"){
                if(typeof console != "undefined") console.error("A field's name attribute is called 'submit' - This will result in problems, rename it");
            }
            var instance = $(this).data("formtablefield");
            if(instance){
                if(!instance.validate()) result = false;
            }
        });
        return result;
    };
    this.validateAndSubmit = function(ev){
        if(ev){
            ev.stopPropagation();
            ev.preventDefault();
        }
        if(self.validateAllFields()){
            self.container.children().trigger("submit");
        }
    }
    this.container.find(".formtable-buttons input[type='submit']").on("click", this.validateAndSubmit);
}

/**
* The formtable field
*
* @param field
* @param validators
*/
function FormTableField(field, validators){
    var self = this;
    this.validators = validators;
    this.field = field;
    this.field.data("formtablefield", this);
    if(this.validators.length) {
        this.field.after('<div class="formtable-validation">');
        this.validationBox = this.field.next();
    }
    this.validate = function(){
        var result = true;
        var errorMessage = null;
        // check for native html5 validation
        var rawField = this.field.get(0);
        if(typeof rawField.validity != "undefined"){
            if(!rawField.validity.valid){
                result = false;
                errorMessage = rawField.validationMessage;
            }
        }

        // if no validators are given than skip
        if(result && !this.validators.length) return true;

        this.validationBox.hide();
        this.field.removeClass("formtable-validation-error");
        var fieldValue = this.field.val();
        if(this.field.is("[type='checkbox']")) fieldValue = this.field.is(":checked") ? this.field.val() : null;
        if(this.field.is("[type='radio']")){
            var c = this.field.closest("form").find("input[name='"+this.field.attr("name")+"']:checked");
            fieldValue = c.length ? c.val() : null;
        }
        if(fieldValue === null){
            fieldValue = [];
        }else if(!$.isArray(fieldValue)) {
            fieldValue = [fieldValue];
        }
        for(var i in this.validators){
            if(!result) break;
            var validator =  this.validators[i];
            switch(validator.type){
                case "Form_Validator_Length":
                    var min = validator.options.min ? parseInt(validator.options.min) : null;
                    var max = validator.options.max ? parseInt(validator.options.max) : null;
                    $.each(fieldValue, function(index, value){
                        var len = value ? value.length : 0;
                        if(min !== null && len < min){
                            result = false;
                            return false;
                        }
                        if(max !== null && len > max){
                            result = false;
                            return false;
                        }
                    });
                break;
                case "Form_Validator_Regex":
                    var regex = new RegExp(validator.options.regex, validator.options.regexoptions);
                    $.each(fieldValue, function(index, value){
                        if(!regex.test(value)){
                            result = false;
                            return false;
                        }
                    });
                break;
                case "Form_Validator_Required":
                    if(!fieldValue.length) result = false;
                    var trimRegex = validator.options.trim ? new RegExp("^["+validator.options.trim+"]|["+validator.options.trim+"]$", "ig") : null;
                    $.each(fieldValue, function(index, value){
                        if(trimRegex) value = value.replace(trimRegex);
                        if(!value.length){
                            result = false;
                            return false;
                        }
                    });
                break;
            }
            if(!result){
                errorMessage = validator.errorMessage;
            }
        }
        return result ? true : this.showError(errorMessage);
    };
    this.showError = function(message){
        this.validationBox.html(message).show();
        return false;
    };
}