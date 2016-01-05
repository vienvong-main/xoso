$(document).ready(function(){
    $('ul.dropdown-menu [data-toggle=dropdown]').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        $(this).parent().siblings().removeClass('open');
        $(this).parent().toggleClass('open');
    });

    var offset = 250;
    var duration = 300;
    $(window).scroll(function () {
        if (jQuery(this).scrollTop() > offset) {
            $('.back-to-top').fadeIn(duration);
        } else {
            $('.back-to-top').fadeOut(duration);
        }
    });

    $('.back-to-top').click(function (event) {
        event.preventDefault();
        $('html, body').animate({scrollTop: 0}, duration);
        return false;
    });

    $("#date-area").datepicker({
        format: "dd/mm/yyyy",
        language: 'vi',
        endDate: '+0d',
        todayBtn: 'linked',
        autoclose: true
    });
});