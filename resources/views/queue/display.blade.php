<!-- resources/views/queue/display.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Display - MediLink Hospital</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            overflow: hidden;
        }

        .header {
            background: rgba(0,0,0,0.3);
            padding: 20px;
            text-align: center;
            border-bottom: 3px solid white;
        }

        .header h1 {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .current-time {
            font-size: 24px;
            opacity: 0.9;
        }

        .content {
            display: flex;
            height: calc(100vh - 150px);
        }

        .now-serving {
            flex: 1;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            margin: 20px;
            border-radius: 20px;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 3px solid rgba(255,255,255,0.3);
        }

        .now-serving-label {
            font-size: 36px;
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .current-queue {
            font-size: 120px;
            font-weight: bold;
            color: #FFD700;
            text-shadow: 0 0 30px rgba(255,215,0,0.5);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .doctor-info {
            margin-top: 30px;
            font-size: 32px;
            text-align: center;
        }

        .counter-name {
            background: rgba(255,255,255,0.2);
            padding: 10px 30px;
            border-radius: 10px;
            margin-top: 15px;
            font-size: 24px;
        }

        .queue-list {
            flex: 1;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            margin: 20px 20px 20px 0;
            border-radius: 20px;
            padding: 30px;
            border: 3px solid rgba(255,255,255,0.3);
        }

        .queue-list h2 {
            font-size: 32px;
            margin-bottom: 20px;
            text-align: center;
            border-bottom: 2px solid rgba(255,255,255,0.3);
            padding-bottom: 15px;
        }

        .waiting-item {
            background: rgba(255,255,255,0.15);
            padding: 15px 20px;
            margin: 10px 0;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 24px;
        }

        .queue-num {
            font-size: 32px;
            font-weight: bold;
            color: #FFD700;
        }

        .footer {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            text-align: center;
            font-size: 18px;
            border-top: 3px solid white;
        }

        .blink {
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0.5; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏥 MEDILINK HOSPITAL - QUEUE SYSTEM</h1>
        <div class="current-time" id="current-time"></div>
    </div>

    <div class="content">
        <!-- Now Serving Section -->
        <div class="now-serving">
            <div class="now-serving-label blink">▶️ NOW SERVING</div>
            <div class="current-queue" id="current-queue">
                @if($nowServing)
                    {{ $nowServing->formatted_queue_number }}
                @else
                    ---
                @endif
            </div>
            @if($nowServing)
            <div class="doctor-info">
                <div>{{ $nowServing->patient->user->name }}</div>
                <div style="opacity: 0.8; font-size: 24px; margin-top: 10px;">
                    Dr. {{ $nowServing->doctor->user->name }}
                </div>
                <div class="counter-name">
                    📍 {{ $nowServing->doctor->specialization }}
                </div>
            </div>
            @endif
        </div>

        <!-- Waiting Queue List -->
        <div class="queue-list">
            <h2>⏳ WAITING QUEUE</h2>
            <div id="waiting-list">
                @forelse($waitingQueue as $waiting)
                <div class="waiting-item">
                    <span class="queue-num">{{ $waiting->formatted_queue_number }}</span>
                    <span>{{ $waiting->patient->user->name }}</span>
                    <span style="opacity: 0.8;">Dr. {{ $waiting->doctor->user->name }}</span>
                </div>
                @empty
                <div style="text-align: center; padding: 40px; opacity: 0.6; font-size: 24px;">
                    No patients waiting
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="footer">
        <span>Please wait for your queue number to be called</span>
        <span style="margin: 0 20px;">•</span>
        <span>Listen for announcements</span>
        <span style="margin: 0 20px;">•</span>
        <span>Have your ticket ready</span>
    </div>

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            document.getElementById('current-time').textContent = 
                now.toLocaleDateString('en-US', options);
        }
        
        updateTime();
        setInterval(updateTime, 1000);

        // Auto-refresh every 10 seconds to update queue
        setTimeout(() => {
            location.reload();
        }, 10000);
    </script>
</body>
</html>