$(document).ready(function() {
    var tables = $('.table');
    tables.each(function( index ) {
        $(this).wrap('<div class="responsive" />');
    });
});
