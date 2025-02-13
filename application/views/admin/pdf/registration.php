<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 50px;
            font-size: 14px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 2px solid #000;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header .company-info {
            width: 48% !important;
        }

        .header logo {
            font-size: 18px;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .receipt-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
            padding: 10px;
            background: #f8f8f8;
            border-bottom: 2px solid #000;
        }

        .payment-details {
            margin-top: 15px;
        }

        h3 {
            margin: 10px 0;
            font-size: 16px;
            color: #333;
        }

        p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }

        .balance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .balance-table th,
        .balance-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .balance-table th {
            background: #f0f0f0;
        }

        .important-note {
            margin-top: 20px;
            font-size: 12px;
            color: #d32f2f;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <table>
            <tbody>
                <tr>
                    <td>
                        <div class="company-info">
                            <logo>BrightRoute</logo>
                            <p>First Floor, Office No 37, 38 and 39, 1184/4,Shreenath Plaza, F C Road,Shivaji Nagar, Pune, Maharashtra, 411005</p>
                        </div>
                    </td>
                    <td>
                        <div class="company-info">
                            <logo>Education Vibes</logo>
                            <p>A Unit of Brightroute Consulting</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <table>
            <tr>
                <td>Receipt</td>
                <td>Invoice No</td>
            </tr>
            <tr>
                <td>Date of Payment</td>
                <td></td>
            </tr>
            <tr>
                <td>22/08/2024</td>
                <td></td>
            </tr>
        </table>
        <br>
        <br>
        <table>
            <tr>
                <td>Student Name</td>
                <td>Payment Received From</td>
                <td>Acadmic year</td>
            </tr>
            <tr>
                <td>Alisha Ashfaque Shah Rajguru</td>
                <td>Alisha Ashfaque Shah Rajguru</td>
                <td>2024 - 2025</td>
            </tr>
        </table>
        <br>
        <br>
        <table>
            <tr>
                <td>Residence Address</td>
                <td>University Name</td>
                <td>Country</td>
            </tr>
            <tr>
                <td>Alisha Ashfaque Shah Rajguru</td>
                <td>Alisha Ashfaque Shah Rajguru</td>
                <td>Uzbekistan</td>
            </tr>
        </table>



       

        <!-- Balance Details Table -->
        <h3>Balance Details</h3>
        <table class="balance-table">
            <tr>
                <th>Total Service Charge</th>
                <th>Amount</th>
            </tr>
            <tr>
                <td>Total Service Charge received till date</td>
                <td>₹90,000.00</td>
                </