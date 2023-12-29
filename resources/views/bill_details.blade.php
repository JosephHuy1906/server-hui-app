<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Details</title>
</head>

<body>
    <div style="width: 100%;display: flex; justify-content: center; margin-top: 5%">
        <div style="width: 30%;padding: 15px; border-radius: 10px; box-shadow: 0 0 5px gray">

            <h1 style="text-align: center; font-size: 2em">Bill Details</h1>
            <h3 style="font-size: 1.5em">Chúc mừng bạn đã thanh toán thành công</h3>
            <p>Transaction ID: {{ $transactionId }}</p>
            <p>Số tiền chuyển khoản: {{ $price / 100 }}đ</p>
            <p>Mô tả: {{ $description }}</p>
            <p style="text-align: center; color: red">Vui lòng quay lại app để xem giao dịch</p>
        </div>
    </div>
</body>

</html>
