<?php
use App\Http\Controllers\CheckoutController;

$checkout = new CheckoutController();
$orderCode = $_GET['orderCode'];

$checkout->updateStatusReject($orderCode);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán thất bại</title>
</head>

<body>
    <div class="main-box">
        <h4 class="payment-titlte">Bạn đã huỷ giao dịch thanh toán </h4>
        <p>Vui lòng quay lại app để kiểm tra giao dịch</p>
    </div>
</body>

</html>
