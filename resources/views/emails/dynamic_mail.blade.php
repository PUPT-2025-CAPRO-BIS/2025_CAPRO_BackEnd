<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            padding: 15px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding: 20px;
            background-color: #017f4f;
            color: #ffffff; 
            border-bottom: 1px solid #ddd;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .header img {
            height: 70px;
            vertical-align: middle;
            margin: 0 5px;
        }

        .header h2 {
            margin: 10px 0 0 0;
            font-size: 20px;
            font-weight: bold;
        }

        .message {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .message p {
            font-size: 14px;
            color: #333;
            margin-bottom: 15px;
        }
        .message strong {
            font-size: 15px;
        }
        .footer {
            text-align: center;
            padding: 10px;
            font-size: 12px;
            color: #777;
            background-color: #f1f1f1;
            border: 1px solid #ddd;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            display: block !important;
        }

    </style>
</head>
<body>

    <div class="container">

        <div class="header">
            <img src="https://000040122.xyz/images/taguig.png" alt="Taguig Logo">
            <img src="http://000040122.xyz/images/central_logo.png" alt="Central Logo">
            <h2>Barangay Central Bicutan</h2>
        </div>

        <div class="message">
            <p>{!! $content !!}</p>
        </div>

        <div class="footer">
            <p>If you have any questions, please contact support.</p>
        </div>

    </div>

</body>

</html>