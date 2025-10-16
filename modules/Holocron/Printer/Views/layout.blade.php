<html lang="en">
<head>
    <meta charset="UTF-8" />
    <style>
        body {
            margin: 0;
            width: 512px;
            box-sizing: border-box;
            font-family: "Courier New", "Monaco", "Menlo", "Consolas", monospace;
            font-size: 24px;
            background-color: #ffffff;
            padding: 0;
        }

        .font-bold {
            font-weight: 700;
        }

        .text-lg {
            font-size: 36px;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }
    </style>

    @yield('style')
</head>
<body>
@yield('content')
</body>
</html>
