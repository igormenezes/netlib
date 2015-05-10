function configAjaxForm(type, data, url, dataType, beforeSend, error, success) {   
   var options = {
        type: type,
        data: data,
        url: url, 
        dataType: dataType,
        beforeSend: function(){
            if(beforeSend)
                $('#ajax').html("<section class=\"floating-alert ajax-loading\"><section><span data-icon=\"&#61819;\"></span></section></section>");
        },
        error: function () {
            console.log(error);
        },
        complete: function() {
            if(beforeSend)
                $('#ajax').html("");
        },
        success: success
    };
    
    return options;
}

