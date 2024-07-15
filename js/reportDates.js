$(document).ready(function() {
    const year = new Date().getFullYear();
    const startDate = year + '-' + '01' + '-' + '01';
    const endDate = year + '-' + '12' + '-' + '31';

    $('#reportCheckin').val(startDate);
    $('#reportCheckout').val(endDate);
});
