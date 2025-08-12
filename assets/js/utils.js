function alert_toast(msg = '', type = 'success') {
    var Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
    Toast.fire({
        icon: type,
        title: msg
    });
}

function start_loader() {
    $('body').append('<div id="preloader"><div class="loader-holder"><div></div><div></div><div></div><div></div></div></div>');
}

function end_loader() {
    $('#preloader').fadeOut('fast', function() {
        $('#preloader').remove();
    });
}
