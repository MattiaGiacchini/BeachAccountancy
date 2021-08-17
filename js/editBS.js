$(document).ready(function() {
    $(".editBSbutton").click(function(){
        let bs = $(this).val();
        window.location = "editBS.php?BS=" + bs;
    });
});
