<?php
// Secure Turnstile Verification - PHP Version (Two Images Left & Right)
session_start();

// Configuration
$secretKey = "0x4AAAAAAFJlrqj4gsLEqQxLj-k1cXggsmk";
$siteKey = "0x4AAAAAAFJlrovk3z9fpjMf";
$redirectUrl = "https://google.com";
$error = "";

// Verification Turnstile
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cf-turnstile-response'])) {
    $token = $_POST['cf-turnstile-response'];
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $data = [
        'secret' => $secretKey,
        'response' => $token,
        'remoteip' => $ip
    ];
    
    $ch = curl_init("https://challenges.cloudflare.com/turnstile/v0/siteverify");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $result = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // ===== DEBUG =====
    error_log("=== TURNSTILE DEBUG ===");
    error_log("cURL ERROR: " . (curl_error($ch) ?: "none"));
    error_log("HTTP STATUS: " . $httpStatus);
    error_log("CF RESPONSE: " . $result);
    error_log("TOKEN: " . substr($token, 0, 20) . "...");
    error_log("=======================");
    
    if (curl_error($ch)) {
        $error = "Verbindungsfehler / Erreur de connexion : " . curl_error($ch);
    }
    curl_close($ch);
    
    if ($result && $httpStatus === 200) {
        $response = json_decode($result);
        if ($response->success === true) {
            header("Location: " . $redirectUrl);
            exit;
        } else {
            $error = "Verifizierung fehlgeschlagen. Bitte versuchen Sie es erneut. / La vérification a échoué. Veuillez réessayer.";
            if (isset($response->{'error-codes'})) {
                $error .= " (" . implode(", ", $response->{'error-codes'}) . ")";
            }
        }
    } else {
        $error = "Serverfehler. Bitte versuchen Sie es später erneut. / Erreur serveur. Veuillez réessayer plus tard.";
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Sicherheitsüberprüfung / Vérification de sécurité</title>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background: #fff5eb;
            position: relative;
            overflow-x: hidden;
        }

        .bg-images {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            display: flex;
        }

        .bg-left {
            width: 50%;
            height: 100%;
            background-image: url('https://www.itsme-id.com/hs-fs/hubfs/Website%2025/Visual%20assets/B2C/Home=Header%2c%20Language=ENG.webp?width=2240&name=Home=Header,%20Language=ENG.webp');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
        }

        .bg-right {
            width: 50%;
            height: 100%;
            background-image: url('https://www.itsme-id.com/hs-fs/hubfs/Website%2025/Visual%20assets/B2C/Home=Login%2c%20Language=EN.png?width=2400&name=Home=Login,%20Language=EN.png');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
        }

        .container {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 40px 28px;
            border-radius: 28px;
            box-shadow: 
                0 25px 60px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 122, 0, 0.2);
            width: 100%;
            max-width: 420px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .logo {
            margin-bottom: 22px;
        }

        .logo img {
            width: 100px;
            height: auto;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(255, 122, 0, 0.15);
        }

        h2 {
            color: #d95c00;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        h3 {
            color: #d95c00;
            font-size: 16px;
            font-weight: 500;
            font-style: italic;
            margin-bottom: 18px;
            opacity: 0.85;
        }

        .description {
            font-size: 15px;
            color: #5a4030;
            margin-bottom: 28px;
            line-height: 1.6;
            padding: 0 8px;
        }

        .description .fr {
            display: block;
            font-size: 13px;
            color: #8a6a50;
            font-style: italic;
            margin-top: 6px;
        }

        .verify-box {
            background: linear-gradient(135deg, #fff8f0 0%, #ffefd9 100%);
            border-radius: 20px;
            padding: 22px 14px;
            margin-bottom: 24px;
            border: 1.5px solid #ffd6a5;
            position: relative;
            overflow: hidden;
        }

        .verify-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff8c1a, #ff6a00, #ff8c1a);
            background-size: 200% 100%;
            animation: shimmer 3s linear infinite;
        }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .cf-turnstile {
            transform: scale(0.95);
            margin: 0 auto;
            display: flex;
            justify-content: center;
        }

        button {
            width: 100%;
            padding: 17px 0;
            background: linear-gradient(135deg, #ff8c1a 0%, #ff6a00 100%);
            border: none;
            color: white;
            border-radius: 16px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s;
            -webkit-tap-highlight-color: transparent;
            box-shadow: 0 10px 25px rgba(255, 106, 0, 0.35);
            letter-spacing: 0.3px;
        }

        button:hover {
            background: linear-gradient(135deg, #ff7a00 0%, #e65c00 100%);
            box-shadow: 0 12px 30px rgba(255, 106, 0, 0.45);
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(0) scale(0.98);
        }

        button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .error {
            color: #d32f2f;
            margin-top: 18px;
            font-weight: 500;
            font-size: 14px;
            background: #ffebee;
            padding: 14px;
            border-radius: 12px;
            border-left: 4px solid #d32f2f;
            text-align: left;
        }

        .footer {
            font-size: 12px;
            color: #a07850;
            margin-top: 28px;
            border-top: 1px solid #ffe0c2;
            padding-top: 20px;
            line-height: 1.6;
        }

        .footer .fr {
            display: block;
            font-style: italic;
            opacity: 0.85;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 992px) {
            .container {
                max-width: 380px;
                padding: 35px 24px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            .container {
                max-width: 100%;
                padding: 32px 20px;
            }
            h2 { font-size: 24px; }
            h3 { font-size: 15px; }
            .description { font-size: 14px; }
        }

        @media (max-width: 480px) {
            body {
                padding: 12px;
            }
            .bg-images {
                flex-direction: column;
            }
            .bg-left,
            .bg-right {
                width: 100%;
                height: 50%;
            }
            .container {
                padding: 28px 18px;
                border-radius: 24px;
            }
            .logo img { width: 85px; }
            h2 { font-size: 22px; }
            h3 { font-size: 14px; }
            .description { font-size: 14px; margin-bottom: 22px; }
            .description .fr { font-size: 12px; }
            .verify-box { padding: 18px 10px; margin-bottom: 20px; }
            .cf-turnstile { transform: scale(0.88); }
            button { padding: 15px 0; font-size: 16px; border-radius: 14px; }
            .footer { font-size: 11px; margin-top: 22px; padding-top: 16px; }
        }

        @media (max-width: 380px) {
            .container { padding: 24px 14px; }
            h2 { font-size: 20px; }
            h3 { font-size: 13px; }
            .description { font-size: 13px; }
            .description .fr { font-size: 11px; }
            .cf-turnstile { transform: scale(0.80); }
            button { padding: 14px 0; font-size: 15px; }
        }

        @media (max-width: 320px) {
            .container { padding: 20px 12px; border-radius: 20px; }
            .logo img { width: 70px; }
            h2 { font-size: 18px; }
            h3 { font-size: 12px; }
            .description { font-size: 12px; }
            .cf-turnstile { transform: scale(0.70); }
            button { padding: 13px 0; font-size: 14px; }
            .footer { font-size: 10px; }
        }

        @media (min-width: 1200px) {
            .container {
                max-width: 460px;
                padding: 48px 34px;
            }
            .logo img { width: 110px; }
            h2 { font-size: 30px; }
            h3 { font-size: 18px; }
            .description { font-size: 16px; }
            button { font-size: 18px; padding: 18px 0; }
        }
    </style>
</head>
<body>
    <div class="bg-images">
        <div class="bg-left"></div>
        <div class="bg-right"></div>
    </div>

    <div class="container">
        <div class="logo">
            <img src="https://www.itsme-id.com/hubfs/Website%2025/Branding/Logo/itsme-logo.svg" alt="Logo">
        </div>

        <h2>Sicherheitsüberprüfung</h2>
        <h3>Vérification de sécurité</h3>

        <p class="description">
            Bitte bestätigen Sie, dass Sie kein Roboter sind
            <span class="fr">Veuillez confirmer que vous n'êtes pas un robot</span>
        </p>

        <form method="POST" id="verifyForm">
            <div class="verify-box">
                <div class="cf-turnstile" data-sitekey="<?php echo $siteKey; ?>" data-callback="onTurnstileSuccess"></div>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <button type="submit" id="submitBtn">Weiter / Continuer</button>
        </form>

        <div class="footer">
            Sicherheit durch Cloudflare Turnstile
            <span class="fr">Sécurisé par Cloudflare Turnstile</span>
            <small style="color: #b88a5c; display: block; margin-top: 8px;">© 2025 Alle Rechte vorbehalten / Tous droits réservés</small>
        </div>
    </div>

    <script>
        function onTurnstileSuccess(token) {
            console.log("Turnstile success:", token);
        }

        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span> Wird überprüft... / Vérification...';
        });
    </script>
</body>
</html>
