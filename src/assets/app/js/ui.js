(function($) {
    $(document).ready(function() {
        $('body').find(':checkbox').each(function(i, el) {
            if ($(el).is(':checked')) {
                $(el).parent().removeClass('unchecked').addClass('checked');
            } else {
                $(el).parent().removeClass('checked').addClass('unchecked');
            }
        });
        $('body').on('click', 'label.form-checkbox', function(evt){
            //console.log($(this).find(':checkbox').is(':checked'), 'status');
            var $checkbox = $(this).find(':checkbox');
            if ($checkbox.is(':checked')) {
                $checkbox.parent().removeClass('unchecked').addClass('checked');
            } else {
                $checkbox.parent().removeClass('checked').addClass('unchecked');
            }
        });
    });
})(jQuery);