<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reject Travel Order</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            background: #f9fafb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        .icon { font-size: 40px; margin-bottom: 16px; }
        h2 { color: #dc2626; font-size: 22px; margin-bottom: 8px; }
        .subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        .info-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #374151;
        }
        .info-box span { display: block; margin-bottom: 4px; }
        .info-box strong { color: #111827; }
        label {
            display: block;
            font-weight: bold;
            font-size: 14px;
            color: #374151;
            margin-bottom: 6px;
        }
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            resize: vertical;
            min-height: 110px;
            font-family: Arial, sans-serif;
            color: #111827;
            transition: border-color 0.2s;
        }
        textarea:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
        }
        .error { color: #dc2626; font-size: 13px; margin-top: 6px; }
        .char-count {
            font-size: 12px;
            color: #9ca3af;
            text-align: right;
            margin-top: 4px;
        }
        button {
            margin-top: 20px;
            width: 100%;
            background: #dc2626;
            color: white;
            padding: 13px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover { background: #b91c1c; }
        button:active { background: #991b1b; }
        .cancel {
            display: block;
            text-align: center;
            margin-top: 14px;
            font-size: 13px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">✖</div>
        <h2>Reject Travel Order</h2>
        <p class="subtitle">
            Please provide a clear reason for rejection.
            The requestor will be notified with this message.
        </p>

        <div class="info-box">
            <span><strong>Travel Order:</strong> {{ $approval->travelOrder->to_code ?? 'Pending' }}</span>
            <span><strong>Requested by:</strong> {{ $approval->travelOrder->user->name ?? 'N/A' }}</span>
            <span><strong>Travel Period:</strong>
                {{ optional($approval->travelOrder->start_date)->format('M d, Y') }}
                –
                {{ optional($approval->travelOrder->end_date)->format('M d, Y') }}
            </span>
        </div>

        @if($errors->any())
            <p class="error">{{ $errors->first('reason') }}</p>
        @endif

        <form method="POST" action="{{ $submitUrl }}">
            @csrf
            <label for="reason">
                Rejection Reason <span style="color:#dc2626">*</span>
            </label>
            <textarea
                name="reason"
                id="reason"
                maxlength="500"
                placeholder="e.g. Missing supporting documents, incorrect travel dates..."
                oninput="document.getElementById('count').textContent = this.value.length"
            >{{ old('reason') }}</textarea>
            <div class="char-count"><span id="count">0</span> / 500</div>
            <button type="submit">Confirm Rejection</button>
        </form>

        <span class="cancel">Changed your mind? You can close this tab.</span>
    </div>
</body>
</html>