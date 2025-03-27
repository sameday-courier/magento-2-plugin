require(
    [
        'jquery',
    ],
    ($) => {
        $(document).ready(() => {
            $(document).on("click", ".showHistoryDetails", function() {
                let show = $(this).val();
                let awbNumber = $(this).data("awb-number");
                let table_id = "history-" + awbNumber;
                if (show === "+") {
                    $("#"+table_id).css("display","block");
                    $(this).val("-");
                    $(this).html("<strong> - </strong>");
                } else {
                    $("#"+table_id).css("display","none");
                    $(this).val("+");
                    $(this).html("<strong> + </strong>");
                }
            });

            $(".showHistoryDetails").trigger("click");
        });
    }
);
