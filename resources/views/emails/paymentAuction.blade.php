<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Thanh toán tiền đấu giá hụi</title>

</head>

<body>
    <div style="
    width: 100%;
    height: 100%;
    background: gray;
    padding: 10px;
    color: black
    ">
        <div class="test-mail"
            style=" width: 90%;
        padding: 15px;
        margin: 0 auto;
        background: #fff;
        border-radius: 10px">
            <h2 style="text-align: center">Put App</h2>
            <p>Xin chào <b>{{ $data->name }}</b>.</p>
            <p>Dưới đây là chi tiết hoá đơn của hội viên.</p>
            <table style="text-align: center; ">
                <tr>
                    <th style="background: #efefef; padding: 10px; color:black">Mã hoá đơn</th>
                    <th style="background: #efefef; padding: 10px; color:black">Số tiền thanh toán</th>
                    <th style="background: #efefef; padding: 10px; color:black">Trạng thái thanh toán</th>
                    <th style="background: #efefef; padding: 10px; color:black">Ngày thanh toán</th>
                </tr>
                <tr>
                    <td style="padding: 5px">#{{ $checkout->id }}</td>
                    <td style="padding: 5px">{{ number_format($checkout->price, 0, ',', '.') }}đ</td>
                    <td style="padding: 5px">
                        @if ($checkout->status === 'approved')
                            Đã thanh toán
                        @elseif($checkout->status === 'rejected')
                            Đã bị huỷ
                        @else
                            {{ $checkout->status }}
                        @endif
                    </td>
                    <td style="padding: 5px">{{ $checkout->created_at }}</td>
                </tr>
            </table>
            <div style=" margin-top: 40px;">
                <p>Trân trọng.</p>
                <p style="
            font-style: italic; margin-top: -10px">The Put Team.</p>
            </div>
        </div>
    </div>
</body>

</html>
