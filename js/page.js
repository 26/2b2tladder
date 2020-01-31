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

function openTab(event, tabName) {
    let tabcontent = document.getElementsByClassName("tab-content");
    for (let i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    let tablinks = document.getElementsByClassName("tablink");
    for (let i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    document.getElementById(tabName).style.opacity = "100";
    document.getElementById(tabName).style.display = "inherit";

    event.currentTarget.className += " active";
}