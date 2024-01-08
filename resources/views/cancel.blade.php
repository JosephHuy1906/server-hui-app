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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán thất bại</title>
    <link rel="stylesheet" href="../View/fontawesome-free-6.1.1-web/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            text-rendering: optimizeSpeed;
            font-family: "Poppins", sans-serif;
            background-color: #fafafa;
        }

        .container {
            display: flex;
            width: 100%;
            height: 100vh;
            background-color: #dedede;
            align-items: center;
            justify-content: center;
        }

        .container__img {
            width: 100%;
            padding-top: 90%;
            background-position: top center;
            background-repeat: no-repeat;
            background-size: cover;
            filter: brightness(.9);
        }

        .modal {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            display: flex;
            height: 100vh;
            background-color: #dedede;
        }

        .modal__message {
            margin: auto;
            width: 80%;
            text-align: center;
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
        }

        .modal-icon {
            text-align: center;
            display: block;
            font-size: 50px;
            color: #5ec9dd;
        }

        .modal__title {
            text-align: center;
            font-size: 1.5em;
            color: #363636;
        }

        .modal__desc {
            text-align: center;
            font-size: 1em;
            color: #999;
        }

        .modal__footer {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal__link {
            display: inline-block;
            padding: 10px 20px;
            font-size: 18px;
            text-decoration: none;
            color: #fff;
            border-radius: 8px;
            margin-left: 20px;
            font-weight: 600;
        }

        .modal__link:first-child {
            margin-left: 0;
        }


        .modal__link.home {
            background-color: #ee4d2d;
            opacity: 0.9;
            width: 150px;
            text-align: center;
            height: 40px;
            line-height: 40px;
            font-size: 1.3em;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="container__img" style="">
        </div>
        <div class="modal">
            <div class="modal__message">
                <img src="./icon-cancel.png" class="success" />
                <h2 class="modal__title">Bạn đã huỷ thanh toán!</h2>
                <p class="modal__desc">
                    Bạn đã huỷ hoá đơn thanh toán với mã hoá đơn <b>#<?php echo $orderCode; ?></b>, vui lòng quay lại app!</p>
                <div class="modal__footer">
                    <a href="#" class="modal__link home">Ok</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
