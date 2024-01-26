<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>User chưa thanh toán tiền</title>

</head>

<body>
    <div style="
    width: 100%;
    height: 100%;
    background: gray;
    color: black
    ">
        <div class="test-mail"
            style="
        width: 90%; 
        padding: 10px;
        margin: 0 auto;
        background: #fff;
        border-radius: 10px;
      ">
            <h2 style="text-align: center">Put App</h2>
            <p>Xin chào Admin {{ $ad->name }}.</p>
            <p>Dưới đây là danh sách user chưa đóng tiền chơi hụi theo phòng {{ $room->title }} trong ngày hôm nay.</p>
            <table style="text-align: center; ">
                <tr>
                    <th style="background: #efefef; padding: 5px; color:black">ID khách hàng</th>
                    <th style="background: #efefef; padding: 5px; color:black">Tên khách hàng</th>
                    <th style="background: #efefef; padding: 5px; color:black">Tên phòng</th>
                    <th style="background: #efefef; padding: 5px; color:black">Số tiền phải đóng</th>
                    <th style="background: #efefef; padding: 5px; color:black">Trạng thái</th>
                </tr>
                @foreach ($check as $item)
                    <tr>
                        <td>{{ $item->user_id }}</td>
                        <td>{{ $item->user->name }}</td>
                        <td>{{ $room->title }}</td>
                        <td>{{ number_format($room->price_room, 0, ',', '.') }}đ</td>
                        <td>Chưa thanh toán</td>
                    </tr>
                @endforeach
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
