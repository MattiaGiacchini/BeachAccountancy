$(document).ready(function() {
    const picker = new Litepicker({
        element: document.getElementById('checkin'),
        elementEnd: document.getElementById('checkout'),
        singleMode: false,
        numberOfColumns: 3,
        numberOfMonths: 3,
        allowRepick: true,

        inlineMode: true,
        selectForward: true,
        switchingMonths: 1,


        tooltipNumber: (totalDays) => {
            if (document.getElementById('numDays')) {
                document.getElementById('numDays').value = totalDays;
            }
            return totalDays;
        }
    });
});
