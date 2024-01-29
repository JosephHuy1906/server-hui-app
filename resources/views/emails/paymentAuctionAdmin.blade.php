<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Hoá đơn của user</title>

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
            <p>Xin chào Admin <b>{{ $data->name }}</b>.</p>
            <p>Dưới đây là hoá đơn của khách hàng vừa khởi tạo.</p>
            <table style="text-align: center; ">
                <tr>
                    <th style="background: #efefef; padding: 10px; color:black">Mã hoá đơn</th>
                    <th style="background: #efefef; padding: 10px; color:black">ID khách hàng</th>
                    <th style="background: #efefef; padding: 10px; color:black">Tên khách hàng</th>
                    <th style="background: #efefef; padding: 10px; color:black">Số điện thoại khách hàng</th>
                    <th style="background: #efefef; padding: 10px; color:black">Số tiền thanh toán</th>
                    <th style="background: #efefef; padding: 10px; color:black">Trạng thái thanh toán</th>
                    <th style="background: #efefef; padding: 10px; color:black">Ngày thanh toán</th>
                </tr>
                <tr>
                    <td style="padding: 5px">#{{ $checkout->id }}</td>
                    <td style="padding: 5px">#{{ $data->id }}</td>
                    <td style="padding: 5px">#{{ $data->name }}</td>
                    <td style="padding: 5px">#{{ $data->phone }}</td>
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
