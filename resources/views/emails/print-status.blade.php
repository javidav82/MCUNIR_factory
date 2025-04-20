<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Print Job Status Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .status {
            font-weight: bold;
            color: #007bff;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Print Job Status Update</h1>
        </div>

        <p>Hello,</p>

        <p>The status of your print job has been updated:</p>

        <ul>
            <li><strong>Document Name:</strong> {{ $printJob->document_name }}</li>
            <li><strong>Status:</strong> <span class="status">{{ ucfirst($printJob->status) }}</span></li>
            <li><strong>Date:</strong> {{ $printJob->updated_at->format('Y-m-d H:i:s') }}</li>
        </ul>

        @if($printJob->status === 'completed')
            <p>Your print job has been completed successfully.</p>
        @elseif($printJob->status === 'failed')
            <p>Your print job has failed. Error message: {{ $printJob->error_message }}</p>
        @endif

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>If you have any questions, please contact the support team.</p>
        </div>
    </div>
</body>
</html> 