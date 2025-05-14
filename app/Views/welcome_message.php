<!DOCTYPE html>
<html>

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>JS Spreadsheet test</title>
    <script src="js/clipboard.js"></script>
    <script src="js/spreadsheet.js"></script>
</head>
<link rel="stylesheet" href="css/spreadsheet.css">
<link rel="stylesheet" href="css/style.css">
<style>
    body {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    }

    h1 {
        color: #4183c4;
    }
</style>

<body>
    <nav class="warehouse-nav">
        <ul>
            <li><a href="/stock/64" target="_blank">Склад CA</a></li>
            <li><a href="/stock/54" target="_blank">Склад DS/TL</a></li>
            <li><a href="/stock/57" target="_blank">Склад CL</a></li>
            <li><a href="/stock/29" target="_blank">Склад AX</a></li>
            <li><a href="/stock/28" target="_blank">Склад TR</a></li>
            <li><a href="/stock/65" target="_blank">Склад IN</a></li>
            <li><a href="/stock/47" target="_blank">Склад BB</a></li>
            <li><a href="/stock/60" target="_blank">Склад IT</a></li>
            <li><a href="/stock/61" target="_blank">Склад PS</a></li>
        </ul>
    </nav>

    <style>
        .warehouse-nav {
            position: sticky;
            top: 0;
            background: #f5f5f5;
            padding: 8px;
            z-index: 10;
            border-bottom: 1px solid #ddd;
        }

        .warehouse-nav ul {
            display: flex;
            flex-direction: column;
            gap: 10px;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .warehouse-nav a {
            display: flex;
            text-decoration: none;
            color: #333;
            padding: 6px 12px;
            background: #e0e0e0;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .warehouse-nav a:hover {
            background: #c0c0c0;
        }
    </style>
</body>

</html>