$(function() {
    $(".dropdown").on('mouseover', function() {
        let dropdown = $(this).next();
        dropdown.addClass('visible');
    });

    $(".dropdown").on('mouseout', function() {
        let dropdown = $(this).next();

        if(!dropdown.is(':hover')) {
            dropdown.removeClass('visible');
        }
    });

    $(".dropdown-menu").on('mouseout', function() {
        let dropdownLink = $(this).prev();

        if(!$(this).is(':hover') && !dropdownLink.is(':hover')) {
            $(this).removeClass('visible');
        }
    });

    $(".userpage").click(function() {
       let username = $(this).data('username');

       window.location = "/profile/" + username;
    });
});