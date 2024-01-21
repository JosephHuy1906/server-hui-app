<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reset password</title>

</head>

<body>
    <div style="
    width: 100%;
    height: 100%;
    background: gray;
    padding: 10px
    ">
        <div class="test-mail"
            style=" width: 60%;
        padding: 15px;
        margin: 0 auto;
        background: #fff;
        border-radius: 10px">
            <h2 style="text-align: center">Put App</h2>
            <p>Hi {{ $name }}.</p>
            <p>Có vẻ bạn đang quên mất mật khẩu của mình. Dưới đây là mã code để bạn đặt lại mật khẩu mới.</p>
            <p>Code: <b>{{ $code }}</b></p>
            <div style=" margin-top: 100px;">
                <p>Trân trọng.</p>
                <p style="
            font-style: italic">The Put Team.</p>
            </div>
        </div>
    </div>
</body>

</html>
