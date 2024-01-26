<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Server Put App</title>
    <style>
        * {
            margin: 0;
            padding: 0
        }

        main {
            width: 100vw;
            height: 100vh;
            --bg-opacity: 1;
            background-color: rgba(26, 32, 44, var(--bg-opacity));
            color: white;
            display: flex;
            justify-content: center;
            align-items: center
        }

        h2 {
            text-align: center;

        }
    </style>
</head>

<body>
    <main style="display: flex; gap: 10px">
        <img src="http://localhost:8000/api/images/users/logo.jpg" alt="" width="50px" height="50px"
            style="border-radius: 5px">
        <h2>Server Put App</h2>
    </main>
</body>

</html>
