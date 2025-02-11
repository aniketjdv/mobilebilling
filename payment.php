<?php
session_start();
include("db_config.php");
$customerID = $_SESSION['user_id'];
// Fetch payment history
$sql = "SELECT PaymentID, BillID, PaymentDate, AmountPaid, PaymentMethod, UsedMinutes, UsedSMS, UsedData
    FROM Payments
    WHERE CustomerID = ?
    ORDER BY PaymentDate DESC ";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();

$result = $stmt->get_result();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="static/css/style.css">
    <link rel="stylesheet" href="static/css/payment.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="static/js/payment.js"></script>
</head>
<body>
     <? include("header.html")?>
   <!-- <div class="container-payment">
        <h1>Your Dues</h1><br>
        <h3>Total Bill</h3>
        <h3><?//echo($_SESSION['AmountDue']);?></h3>
        <form action="payment.php" method="POST">

        </form>
    </div> -->


   

    <h2>Latest Pending Bill</h2>
    <table id="billTable">
        <thead>
            <tr>
                <th>Bill ID</th>
                <th>Amount Due</th>
                <th>Due Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Payment Modal -->
    <div id="paymentModal" style="display: none;">
        <h2>Complete Your Payment</h2>
        <p>Amount: ₹<span id="payAmount"></span></p>

        <label><input type="radio" name="paymentMethod" value="Credit Card"> Credit Card</label>
        <label><input type="radio" name="paymentMethod" value="Debit Card"> Debit Card</label>
        <label><input type="radio" name="paymentMethod" value="UPI"> UPI</label>

        <button id="confirmPayment">Pay Now</button>
        <button class="close">Cancel</button>
    </div>


    <h2>Your Payment History</h2>
  
    <?php if ($result->num_rows > 0): ?>
        <table border="1">
            <thead>
                <tr>
                    
                    <th>Payment Date</th>
                    <th>Amount Paid (₹)</th>
                    <th>Payment Method</th>
                    <th>Used Minutes</th>
                    <th>Used SMS</th>
                    <th>Used Data (GB)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                       
                        <td><?= htmlspecialchars($row['PaymentDate']) ?></td>
                        <td>&#x20B9; <?= number_format($row['AmountPaid'], 2) ?></td>
                        <td><?= htmlspecialchars($row['PaymentMethod']) ?></td>
                        <td><?= htmlspecialchars($row['UsedMinutes']) ?> mins</td>
                        <td><?= htmlspecialchars($row['UsedSMS']) ?> SMS</td>
                        <td><?= htmlspecialchars($row['UsedData']) ?> GB</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php  else: ?>
        <p>No payment history found.</p>
    <?php endif; ?>



    <script src="payment.js"></script>

</body>
</html>