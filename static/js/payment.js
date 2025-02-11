$(document).ready(function() {
    function loadLatestBill() {
        $.get("fetch_due_bills.php", function(data) {
            let bill = JSON.parse(data);
            if (bill) {
                $("#billTable tbody").html(`
                    <tr>
                        <td>${bill.BillID}</td>
                        <td>₹${bill.AmountDue}</td>
                        <td>${bill.DueDate}</td>
                        <td><button class="payBtn" data-id="${bill.BillID}" data-amount="${bill.AmountDue}">Pay</button></td>
                    </tr>
                `);
            } else {
                $("#billTable tbody").html("<tr><td colspan='4'>No pending bills</td></tr>");
            }
        });
    }

    loadLatestBill();

    $(document).on("click", ".payBtn", function() {
        let amount = $(this).data("amount");
        let billID = $(this).data("id");

        $("#payAmount").text(amount);
        $("#confirmPayment").data("id", billID);
        $("#paymentModal").show();
    });

    $(".close").click(function() {
        $("#paymentModal").hide();
    });

    $("#confirmPayment").click(function() {
        let billID = $(this).data("id");
        let paymentMethod = $("input[name='paymentMethod']:checked").val();

        if (!paymentMethod) {
            alert("Please select a payment method.");
            return;
        }

        $.post("process_payment.php", { billID: billID, paymentMethod: paymentMethod }, function(response) {
            alert(response);
            $("#paymentModal").hide();
            loadLatestBill();
        });
    });
});
