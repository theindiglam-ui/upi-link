<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');

/**
 * ============================================================
 * UPI PAYMENT CHECKOUT - ADVANCED PHONEPE & P2P COMPATIBILITY
 * File: pay.php / api/pay.php
 * ============================================================
 */

function showError($message) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Link - Error</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;
    background:#0b1315;
    color:#e2e8f0;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}
.error-card{
    width:100%;
    max-width:420px;
    background:#131f24;
    border:1px solid #23373e;
    border-radius:24px;
    padding:36px 24px;
    text-align:center;
    box-shadow:0 25px 60px rgba(0,0,0,0.5);
}
.error-icon{
    width:60px;
    height:60px;
    margin:0 auto 18px;
    border-radius:50%;
    background:rgba(239,68,68,0.15);
    color:#ef4444;
    border:1px solid rgba(239,68,68,0.3);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:28px;
    font-weight:700;
}
.error-title{font-size:18px;font-weight:700;color:#f8fafc;margin-bottom:8px;}
.error-text{color:#94a3b8;font-size:13px;line-height:1.5;}
</style>
</head>
<body>
<div class="error-card">
    <div class="error-icon">!</div>
    <div class="error-title">Payment Link Error</div>
    <div class="error-text"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
</div>
</body>
</html>
<?php
    exit;
}

$data = isset($_GET['data']) ? trim($_GET['data']) : '';
if($data === ''){
    showError("Invalid payment link parameters.");
}

$decodedBase64 = base64_decode($data, true);
if($decodedBase64 === false){
    $decodedBase64 = base64_decode(str_replace(' ', '+', $data), true);
}
if($decodedBase64 === false){
    showError("Unable to decode payment link data.");
}

$payment = json_decode($decodedBase64, true);
if(!is_array($payment) || json_last_error() !== JSON_ERROR_NONE){
    showError("Invalid payment payload.");
}

$company = trim((string)($payment['company'] ?? 'Payment'));
$logo    = trim((string)($payment['logo'] ?? ''));
$upi     = trim((string)($payment['upi'] ?? ''));
$amount  = trim((string)($payment['amount'] ?? '0'));
$expires = (int)($payment['expires'] ?? 0);

if($company === '' || $upi === '' || $amount === '' || $expires <= 0){
    showError("Payment link data is incomplete.");
}
if(!is_numeric($amount) || (float)$amount <= 0){
    showError("Invalid payment amount specified.");
}
if(strpos($upi, '@') === false){
    showError("Invalid receiver UPI ID.");
}

$currentTime = time();
$isExpired   = ($currentTime >= $expires);

$cleanCompany = preg_replace('/[^a-zA-Z0-9 ]/', '', $company);
if(trim($cleanCompany) === '') {
    $cleanCompany = 'Merchant';
}
$cleanCompany = substr($cleanCompany, 0, 25);

$displayAmount = number_format((float)$amount, 2, '.', '');
$txnRef = 'TXN' . time() . rand(100, 999);

// Standard UPI Query with full reference
$standardUPI = 'upi://pay?pa=' . rawurlencode($upi) .
               '&pn=' . rawurlencode($cleanCompany) .
               '&am=' . rawurlencode($displayAmount) .
               '&cu=INR' .
               '&tr=' . rawurlencode($txnRef) .
               '&tn=' . rawurlencode("Payment to " . $cleanCompany) .
               '&mode=02';

$qrURL = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&margin=10&data=' . rawurlencode($standardUPI);

$safeCompany = htmlspecialchars($company, ENT_QUOTES, 'UTF-8');
$safeUPI     = htmlspecialchars($upi, ENT_QUOTES, 'UTF-8');
$safeLogo    = htmlspecialchars($logo, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="theme-color" content="#5f259f">
<meta name="color-scheme" content="light dark">
<title><?php echo $safeCompany; ?> - Pay ₹<?php echo $displayAmount; ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --phonepe: #5f259f;
    --phonepe-hover: #501d87;
    --phonepe-glow: rgba(95, 37, 159, 0.35);
    --bg: #090e13;
    --card-bg: #121921;
    --card-border: #1e2936;
    --text-main: #f8fafc;
    --text-muted: #94a3b8;
    --accent-blue: #38bdf8;
    --accent-orange: #f59e0b;
    --accent-green: #10b981;
}

*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent;}

body{
    font-family:'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    background: var(--bg);
    background-image: 
        radial-gradient(at 0% 0%, rgba(95, 37, 159, 0.20) 0px, transparent 50%),
        radial-gradient(at 100% 100%, rgba(13, 148, 136, 0.12) 0px, transparent 50%);
    color: var(--text-main);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 20px 14px 40px;
}

.checkout-wrapper{
    width: 100%;
    max-width: 440px;
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 26px;
    padding: 24px 20px;
    box-shadow: 0 30px 70px rgba(0, 0, 0, 0.65);
}

.top-bar{
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

.brand-info{
    display: flex;
    align-items: center;
    gap: 12px;
}

.brand-logo{
    width: 44px;
    height: 44px;
    border-radius: 12px;
    object-fit: contain;
    background: #ffffff;
    padding: 4px;
    border: 1px solid var(--card-border);
}

.brand-logo-fallback{
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #5f259f, #7c3aed);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 800;
}

.brand-name{
    font-size: 15px;
    font-weight: 700;
    color: #ffffff;
}

.brand-sub{
    font-size: 11px;
    color: var(--text-muted);
}

.timer-badge{
    background: rgba(245, 158, 11, 0.12);
    border: 1px solid rgba(245, 158, 11, 0.25);
    color: var(--accent-orange);
    padding: 5px 10px;
    border-radius: 16px;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* AMOUNT CARD */
.amount-card{
    background: linear-gradient(180deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%);
    border: 1px solid var(--card-border);
    border-radius: 20px;
    padding: 18px;
    text-align: center;
    margin-bottom: 18px;
}

.amount-label{
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-muted);
    font-weight: 600;
    margin-bottom: 4px;
}

.amount-value{
    font-size: 36px;
    font-weight: 800;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 3px;
}

.amount-symbol{
    color: #a855f7;
    font-size: 26px;
}

/* PHONEPE HERO BUTTON */
.phonepe-hero-btn{
    width: 100%;
    background: linear-gradient(135deg, #5f259f 0%, #7c3aed 100%);
    color: #ffffff;
    border: none;
    border-radius: 18px;
    padding: 16px;
    font-size: 16px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 10px 25px var(--phonepe-glow);
    margin-bottom: 12px;
}

.phonepe-hero-btn:active{
    transform: scale(0.98);
}

.phonepe-btn-left{
    display: flex;
    align-items: center;
    gap: 12px;
}

.phonepe-badge{
    background: rgba(255, 255, 255, 0.22);
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.5px;
}

/* SECONDARY PHONEPE SCANNER BUTTON */
.phonepe-scan-btn{
    width: 100%;
    background: rgba(95, 37, 159, 0.15);
    border: 1px dashed rgba(168, 85, 247, 0.4);
    color: #c084fc;
    border-radius: 14px;
    padding: 12px;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    margin-bottom: 20px;
    transition: all 0.2s ease;
}

.phonepe-scan-btn:active{
    background: rgba(95, 37, 159, 0.35);
}

/* SECTION HEADING */
.section-label{
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--text-muted);
    font-weight: 700;
    margin-bottom: 10px;
    text-align: center;
}

/* APP GRID */
.app-grid{
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 9px;
    margin-bottom: 20px;
}

.app-btn{
    background: #17242a;
    border: 1px solid var(--card-border);
    border-radius: 14px;
    padding: 12px 6px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #ffffff;
}

.app-btn:hover, .app-btn:active{
    background: #1c2e36;
    border-color: #a855f7;
    transform: translateY(-2px);
}

.app-icon{
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.app-title{
    font-size: 11px;
    font-weight: 700;
    color: #ffffff;
}

/* QR SECTION */
.qr-section{
    background: #ffffff;
    border-radius: 18px;
    padding: 16px;
    text-align: center;
    margin-bottom: 16px;
}

.qr-box{
    width: 190px;
    height: 190px;
    margin: 0 auto 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.qr-image{
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 8px;
}

.qr-hint{
    color: #334155;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

/* COPY UPI BAR */
.copy-bar{
    background: #17242a;
    border: 1px dashed var(--card-border);
    border-radius: 14px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.copy-label{
    font-size: 9px;
    color: var(--text-muted);
    text-transform: uppercase;
    font-weight: 700;
}

.copy-upi{
    font-size: 12px;
    font-weight: 700;
    color: var(--accent-blue);
    margin-top: 2px;
}

.copy-action-btn{
    background: rgba(56, 189, 248, 0.15);
    border: 1px solid rgba(56, 189, 248, 0.3);
    color: var(--accent-blue);
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}

/* FOOTER NOTE */
.footer-note{
    text-align: center;
    font-size: 11px;
    color: var(--text-muted);
    line-height: 1.5;
}

/* TOAST */
.toast{
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: #7c3aed;
    color: #ffffff;
    padding: 12px 22px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    box-shadow: 0 15px 35px rgba(0,0,0,0.5);
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 9999;
    white-space: nowrap;
    text-align: center;
}

.toast.show{
    transform: translateX(-50%) translateY(0);
    opacity: 1;
}

/* EXPIRED STATE */
.expired-card{
    display: none;
    text-align: center;
    padding: 30px 10px;
}

.expired-icon{
    width: 50px;
    height: 50px;
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239,68,68,0.3);
    color: #ef4444;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin: 0 auto 14px;
}
</style>
</head>
<body>

<div class="checkout-wrapper">
    <div id="activeContent" style="<?php echo $isExpired ? 'display:none;' : 'block'; ?>">
        
        <!-- TOP BRAND & TIMER -->
        <div class="top-bar">
            <div class="brand-info">
                <?php if($logo !== ''): ?>
                    <img src="<?php echo $safeLogo; ?>" alt="<?php echo $safeCompany; ?>" class="brand-logo" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <div class="brand-logo-fallback" style="display:none;"><?php echo strtoupper(substr($cleanCompany, 0, 1)); ?></div>
                <?php else: ?>
                    <div class="brand-logo-fallback"><?php echo strtoupper(substr($cleanCompany, 0, 1)); ?></div>
                <?php endif; ?>
                <div>
                    <div class="brand-name"><?php echo $safeCompany; ?></div>
                    <div class="brand-sub">UPI Checkout</div>
                </div>
            </div>

            <div class="timer-badge">
                <span>⏱</span>
                <span id="countdown">--:--</span>
            </div>
        </div>

        <!-- AMOUNT CARD -->
        <div class="amount-card">
            <div class="amount-label">Amount to Pay</div>
            <div class="amount-value">
                <span class="amount-symbol">₹</span>
                <span><?php echo $displayAmount; ?></span>
            </div>
        </div>

        <!-- PHONEPE HERO 1-CLICK BUTTON -->
        <button type="button" class="phonepe-hero-btn" onclick="openPhonePeSmart()">
            <div class="phonepe-btn-left">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="#fff"><path d="M19.5 3h-15C3.1 3 2 4.1 2 5.5v13C2 19.9 3.1 21 4.5 21h15c1.4 0 2.5-1.1 2.5-2.5v-13C22 4.1 20.9 3 19.5 3zm-6.1 14.5l-3.2-4.6v4.6H8.4V6.5h3.6c2.4 0 4 1.5 4 3.7 0 1.6-.9 2.9-2.2 3.4l3.5 4.9h-2.1zM12 12c1.2 0 2-.8 2-1.8s-.8-1.8-2-1.8h-1.8V12H12z"/></svg>
                <span>Pay via PhonePe</span>
            </div>
            <span class="phonepe-badge">FAST PAY</span>
        </button>

        <!-- PHONEPE INSTANT SCANNER LAUNCH -->
        <button type="button" class="phonepe-scan-btn" onclick="openPhonePeScanner()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            <span>Open PhonePe Scanner (100% Reliable)</span>
        </button>

        <div class="section-label">Or Pay with Other Apps</div>

        <!-- OTHER APPS -->
        <div class="app-grid">
            <!-- Paytm -->
            <button type="button" class="app-btn" onclick="triggerUPI('paytm')">
                <div class="app-icon" style="background:#00b9f5;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff"><path d="M21 4H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-9 11.5c-2.5 0-4.5-2-4.5-4.5s2-4.5 4.5-4.5 4.5 2 4.5 4.5-2 4.5-4.5 4.5z"/></svg>
                </div>
                <span class="app-title">Paytm</span>
            </button>

            <!-- Google Pay -->
            <button type="button" class="app-btn" onclick="triggerUPI('gpay')">
                <div class="app-icon" style="background:#fff;">
                    <svg width="18" height="18" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                </div>
                <span class="app-title">Google Pay</span>
            </button>

            <!-- Any UPI -->
            <button type="button" class="app-btn" onclick="triggerUPI('generic')">
                <div class="app-icon" style="background:#10b981;">
                    <span style="font-size:10px;font-weight:900;color:#fff;">UPI</span>
                </div>
                <span class="app-title">All UPI</span>
            </button>
        </div>

        <!-- QR SCANNER (BYPASSES ALL INTENT SECURITY RULES) -->
        <div class="qr-section">
            <div class="qr-box">
                <img src="<?php echo $qrURL; ?>" alt="Scan to Pay" class="qr-image" id="qrImg">
            </div>
            <div class="qr-hint">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#5f259f" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                Scan or Screenshot in PhonePe Scanner
            </div>
        </div>

        <!-- COPY BAR -->
        <div class="copy-bar">
            <div>
                <div class="copy-label">UPI ID</div>
                <div class="copy-upi"><?php echo $safeUPI; ?></div>
            </div>
            <button type="button" class="copy-action-btn" onclick="copyUPI()">Copy ID</button>
        </div>

        <div class="footer-note">
            🔒 Direct UPI transfer • Zero merchant surcharge
        </div>

    </div>

    <!-- EXPIRED STATE -->
    <div id="expiredContent" class="expired-card" style="<?php echo $isExpired ? 'display:block;' : 'display:none;'; ?>">
        <div class="expired-icon">!</div>
        <h2 style="color:#f8fafc;font-size:18px;margin-bottom:6px;">Payment Link Expired</h2>
        <p style="color:var(--text-muted);font-size:12px;line-height:1.5;">This payment link has reached its validity time limit.</p>
    </div>
</div>

<div id="copyToast" class="toast">
    <span id="toastMessage">Opening PhonePe...</span>
</div>

<script>
const upiID          = "<?php echo addslashes($upi); ?>";
const company        = "<?php echo addslashes($cleanCompany); ?>";
const amount         = "<?php echo addslashes($displayAmount); ?>";
const txnRef         = "<?php echo addslashes($txnRef); ?>";
const expiryTimestamp = <?php echo $expires; ?>;

const isAndroid = /android/i.test(navigator.userAgent);
const isIOS     = /iphone|ipad|ipod/i.test(navigator.userAgent);

// Full standard query with reference & mode=02
const fullUPIQuery = "pa=" + encodeURIComponent(upiID) +
                     "&pn=" + encodeURIComponent(company) +
                     "&am=" + encodeURIComponent(amount) +
                     "&cu=INR" +
                     "&tr=" + encodeURIComponent(txnRef) +
                     "&tn=" + encodeURIComponent("Payment to " + company) +
                     "&mode=02";

// P2P Safe Query
const safeP2PQuery = "pa=" + encodeURIComponent(upiID) +
                     "&pn=" + encodeURIComponent(company) +
                     "&cu=INR";

/**
 * Smart PhonePe Launch:
 * Dispatches PhonePe intent with standard transaction reference and fallback
 */
function openPhonePeSmart() {
    if (Math.floor(Date.now() / 1000) >= expiryTimestamp) {
        showExpired();
        return;
    }

    copyToClipboardSilent(amount);
    showToast("Opening PhonePe...");

    let phonePeURL = "";
    if (isAndroid) {
        // Multi-level Intent structure targeting PhonePe
        phonePeURL = "intent://pay?" + fullUPIQuery + "#Intent;scheme=upi;package=com.phonepe.app;action=android.intent.action.VIEW;end";
    } else if (isIOS) {
        phonePeURL = "phonepe://pay?" + fullUPIQuery;
    } else {
        phonePeURL = "phonepe://pay?" + fullUPIQuery;
    }

    setTimeout(() => {
        window.location.href = phonePeURL;
    }, 250);
}

/**
 * Direct PhonePe Scanner launcher (100% bypass for personal VPAs)
 */
function openPhonePeScanner() {
    copyToClipboardSilent(upiID);
    showToast("UPI ID Copied! Opening PhonePe Scanner...");

    let scanURL = "";
    if (isAndroid) {
        scanURL = "intent:#Intent;action=com.phonepe.app.ui.activity.MainActivity;package=com.phonepe.app;end";
    } else {
        scanURL = "phonepe://scan";
    }

    setTimeout(() => {
        window.location.href = scanURL;
    }, 300);
}

function triggerUPI(appKey) {
    if (Math.floor(Date.now() / 1000) >= expiryTimestamp) {
        showExpired();
        return;
    }

    copyToClipboardSilent(amount);

    let targetURL = "";
    if (appKey === "paytm") {
        if (isAndroid) {
            targetURL = "intent://pay?" + fullUPIQuery + "#Intent;scheme=upi;package=net.one97.paytm;end";
        } else {
            targetURL = "paytmmp://pay?" + fullUPIQuery;
        }
        showToast("Opening Paytm...");
    } else if (appKey === "gpay") {
        if (isAndroid) {
            targetURL = "intent://pay?" + fullUPIQuery + "#Intent;scheme=upi;package=com.google.android.apps.nbu.paisa.user;end";
        } else {
            targetURL = "tez://upi/pay?" + fullUPIQuery;
        }
        showToast("Opening Google Pay...");
    } else {
        targetURL = "upi://pay?" + fullUPIQuery;
        showToast("Opening UPI App...");
    }

    setTimeout(() => {
        window.location.href = targetURL;
    }, 250);
}

function copyUPI() {
    copyToClipboardSilent(upiID);
    showToast("UPI ID: " + upiID + " Copied!");
}

function copyToClipboardSilent(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).catch(() => {});
    } else {
        const el = document.createElement("input");
        el.value = text;
        el.style.position = "fixed";
        el.style.left = "-9999px";
        document.body.appendChild(el);
        el.select();
        try { document.execCommand("copy"); } catch(e) {}
        document.body.removeChild(el);
    }
}

function showToast(msg) {
    const toast = document.getElementById("copyToast");
    const label = document.getElementById("toastMessage");
    label.textContent = msg;
    toast.classList.add("show");
    setTimeout(() => { toast.classList.remove("show"); }, 3000);
}

function updateCountdown() {
    const now = Math.floor(Date.now() / 1000);
    const diff = expiryTimestamp - now;

    if (diff <= 0) {
        showExpired();
        return;
    }

    const m = Math.floor(diff / 60);
    const s = diff % 60;
    const badge = document.getElementById("countdown");
    if (badge) {
        badge.textContent = (m < 10 ? "0" + m : m) + ":" + (s < 10 ? "0" + s : s);
    }
}

function showExpired() {
    const active = document.getElementById("activeContent");
    const expired = document.getElementById("expiredContent");
    if (active) active.style.display = "none";
    if (expired) expired.style.display = "block";
}

<?php if(!$isExpired): ?>
updateCountdown();
setInterval(updateCountdown, 1000);
<?php endif; ?>
</script>

</body>
</html>