<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Under Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #1e293b;
            color: #fff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Open Sans', sans-serif;
            margin: 0;
            overflow: hidden;
        }
        .maintenance-container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
            z-index: 1;
        }
        .moon {
            width: 100px;
            height: 100px;
            background: #f1f5f9;
            border-radius: 50%;
            margin: 0 auto 2rem;
            box-shadow: 0 0 50px rgba(241, 245, 249, 0.3);
            position: relative;
        }
        .moon::after {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            width: 20px;
            height: 20px;
            background: rgba(0,0,0,0.05);
            border-radius: 50%;
            box-shadow: 25px 35px 0 rgba(0,0,0,0.05), 45px 15px 0 rgba(0,0,0,0.05);
        }
        h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        p {
            font-size: 1.25rem;
            color: #94a3b8;
            margin-bottom: 2rem;
        }
        .btn-reload {
            background: #4680ff;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            color: #fff;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .btn-reload:hover {
            background: #3b66cc;
            transform: translateY(-2px);
            color: #fff;
        }
        .stars {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        .star {
            position: absolute;
            background: #fff;
            border-radius: 50%;
            opacity: 0.5;
            animation: twinkle var(--duration) infinite ease-in-out;
        }
        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }
    </style>
</head>
<body>
    <div class="stars" id="stars"></div>
    
    <div class="maintenance-container">
        <div class="moon"></div>
        <h1>Maintenance</h1>
        <p>Our system is currently undergoing scheduled maintenance. We'll be back shortly!</p>
        <a href="javascript:location.reload()" class="btn-reload">Try Again</a>
    </div>

    <script>
        const starsContainer = document.getElementById('stars');
        const starCount = 100;

        for (let i = 0; i < starCount; i++) {
            const star = document.createElement('div');
            star.className = 'star';
            const size = Math.random() * 3;
            star.style.width = `${size}px`;
            star.style.height = `${size}px`;
            star.style.left = `${Math.random() * 100}%`;
            star.style.top = `${Math.random() * 100}%`;
            star.style.setProperty('--duration', `${Math.random() * 3 + 2}s`);
            star.style.animationDelay = `${Math.random() * 5}s`;
            starsContainer.appendChild(star);
        }
    </script>
</body>
</html>
