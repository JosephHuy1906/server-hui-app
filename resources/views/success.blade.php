<?php
use App\Http\Controllers\CheckoutController;

$checkout = new CheckoutController();
$orderCode = $_GET['orderCode'];

$checkout->updateStatus($orderCode);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán thành công</title>
</head>

<body>
    <div class="main-box">
        <h4 class="payment-titlte">
            Chúc mừng bạn đã thanh toán thành công. Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi
        </h4>
        <p>Vui lòng quay lại app để kiểm tra giao dịch</p>
    </div>
</body>

</html>
