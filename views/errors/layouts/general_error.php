<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','Error')</title>

    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background-color: #f0f8ff;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        span {
            text-transform: uppercase;
        }
    </style>
</head>

<body>

    <main>
        @yield('content')
    </main>

</body>

</html>