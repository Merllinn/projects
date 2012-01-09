    $(document).ready(function(){

    //Timepicker init
    $('.timePicker').timepicker({});
    	
    $('input').keydown(function(e){
        if (e.keyCode == 13) {
            $(this).parents('form').find(".submit").trigger("click");
            return false;
        }
    });
    
    $(".confirm").live("click", function(){
		conf = confirm("Are you sure?");
		if(conf==true){
			return true;
		}
		else{
			return false;
		}
    });

    /* vysviceni radku tabulky pri najeti */
    $(".highlight tbody td").live('mouseover mouseout', function(event){
            if (event.type == 'mouseover') {
                $(this).parent("tr").addClass("hover");
             } else {
                $(this).parent("tr").removeClass("hover");
            }
        });
    

    /* Volání AJAXu u všech odkazů s třídou ajax */
    $("a.ajax, #snippet--comments .paginator a").live("click", function (event) {
        event.preventDefault();
        $("#load_dialog").show();
        $.get(this.href);
    });
    
    /* AJAXové odeslání formulářů */
    $("form.ajax").live("submit", function () {
        $("#load_dialog").show();
        $(this).ajaxSubmit();
        return false;
    });
    
    $("form.ajax :submit").live("click", function () {
        $("#load_dialog").show();
        $(this).ajaxSubmit();
        return false;
    });

    /* tooltip init */
    $(".tooltip").tooltip({
        showURL: false
    });
    

    /* datepicker localization */
    jQuery(function($) {
        $.datepicker.regional['cz'] = {
            closeText: 'Zavřít',
            prevText: '&#x3c;Dříve',
            nextText: 'Později&#x3e;',
            currentText: 'Nyní',
            monthNames: ['leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec', 'srpen',
                'září', 'říjen', 'listopad', 'prosinec'],
            monthNamesShort: ['led', 'úno', 'bře', 'dub', 'kvě', 'čer', 'čvc', 'srp', 'zář', 'říj', 'lis', 'pro'],
            dayNames: ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'],
            dayNamesShort: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
            dayNamesMin: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
            weekHeader: 'Týd',
            dateFormat: 'dd.mm.yy',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''
        };
        $.datepicker.setDefaults($.datepicker.regional['cz']);
    });

    /* datepicker init */
    $("input.date").each(function () { // input[type=date] does not work in IE
        var el = $(this);
        var value = el.val();
        var date = (value ? $.datepicker.parseDate($.datepicker.W3C, value) : null);

        var minDate = el.attr("min") || null;
        if (minDate) minDate = $.datepicker.parseDate($.datepicker.W3C, minDate);
        var maxDate = el.attr("max") || null;
        if (maxDate) maxDate = $.datepicker.parseDate($.datepicker.W3C, maxDate);

        // input.attr("type", "text") throws exception
        if (el.attr("type") == "date") {
            var tmp = $("<input/>");
            $.each("class,disabled,id,maxlength,name,readonly,required,size,style,tabindex,title,value".split(","), function(i, attr)  {
                tmp.attr(attr, el.attr(attr));
            });
            el.replaceWith(tmp);
            el = tmp;
        }
        el.datepicker({
            minDate: minDate,
            maxDate: maxDate,
            changeMonth:true,
            changeYear:true,
            showAnim:"slideDown",
            showOn: "both",
            buttonImage: '/images/datepicker.gif',
            buttonImageOnly: true,
            autoSize: true,
            yearRange: '1900:2100'
        });
        el.val($.datepicker.formatDate('dd.mm.yy', date));
    });
    
});