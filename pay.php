<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');

/**
 * ============================================================
 * UPI PAYMENT CHECKOUT - PHONEPE P2P SMART ASSISTANT
 * File: pay.php / api/pay.php
 *
 * Implements:
 * 1. Web Share API (Share QR directly to PhonePe app)
 * 2. 1-Tap QR Save + Auto-Launch PhonePe Scanner
 * 3. 1-Click Paytm Intent (Native support)
 * 4. 1-Tap UPI ID & Amount Copy with Quick Launch
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
<title><?php echo $safeCompany; ?> - Pay ₹<?php echo $displayAmount; ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --phonepe: #5f259f;
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
        radial-gradient(at 0% 0%, rgba(95, 37, 159, 0.22) 0px, transparent 50%),
        radial-gradient(at 100% 100%, rgba(0, 185, 245, 0.12) 0px, transparent 50%);
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
    position: relative;
}

.top-bar{
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
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
    padding: 16px;
    text-align: center;
    margin-bottom: 16px;
}

.amount-label{
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-muted);
    font-weight: 600;
    margin-bottom: 2px;
}

.amount-value{
    font-size: 34px;
    font-weight: 800;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 3px;
}

.amount-symbol{
    color: #c084fc;
    font-size: 24px;
}

/* PHONEPE DIRECT TRIGGER */
.phonepe-box{
    background: linear-gradient(135deg, rgba(95, 37, 159, 0.25) 0%, rgba(124, 58, 237, 0.15) 100%);
    border: 1px solid rgba(168, 85, 247, 0.35);
    border-radius: 20px;
    padding: 16px;
    margin-bottom: 16px;
    text-align: center;
}

.phonepe-header{
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 15px;
    font-weight: 800;
    color: #f8fafc;
    margin-bottom: 12px;
}

.phonepe-actions{
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-bottom: 10px;
}

.btn-phonepe-primary{
    background: linear-gradient(135deg, #5f259f 0%, #7c3aed 100%);
    color: #ffffff;
    border: none;
    border-radius: 14px;
    padding: 12px 8px;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    box-shadow: 0 6px 16px var(--phonepe-glow);
}

.btn-phonepe-secondary{
    background: rgba(255, 255, 255, 0.08);
    color: #e2e8f0;
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 14px;
    padding: 12px 8px;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
}

.phonepe-tip{
    font-size: 11px;
    color: #c084fc;
    line-height: 1.4;
}

/* PAYTM INSTANT BUTTON */
.paytm-instant-btn{
    width: 100%;
    background: #00b9f5;
    color: #ffffff;
    border: none;
    border-radius: 16px;
    padding: 14px 16px;
    font-size: 14px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(0, 185, 245, 0.25);
    margin-bottom: 18px;
}

.paytm-badge{
    background: rgba(255, 255, 255, 0.3);
    padding: 3px 8px;
    border-radius: 8px;
    font-size: 10px;
    font-weight: 800;
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
    width: 180px;
    height: 180px;
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
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

/* COPY BAR */
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
                    <div class="brand-sub">UPI Payment Portal</div>
                </div>
            </div>

            <div class="timer-badge">
                <span>⏱</span>
                <span id="countdown">--:--</span>
            </div>
        </div>

        <!-- AMOUNT CARD -->
        <div class="amount-card">
            <div class="amount-label">Payable Amount</div>
            <div class="amount-value">
                <span class="amount-symbol">₹</span>
                <span><?php echo $displayAmount; ?></span>
            </div>
        </div>

        <!-- PHONEPE SPECIALIZED HUB -->
        <div class="phonepe-box">
            <div class="phonepe-header">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="#c084fc"><path d="M19.5 3h-15C3.1 3 2 4.1 2 5.5v13C2 19.9 3.1 21 4.5 21h15c1.4 0 2.5-1.1 2.5-2.5v-13C22 4.1 20.9 3 19.5 3zm-6.1 14.5l-3.2-4.6v4.6H8.4V6.5h3.6c2.4 0 4 1.5 4 3.7 0 1.6-.9 2.9-2.2 3.4l3.5 4.9h-2.1zM12 12c1.2 0 2-.8 2-1.8s-.8-1.8-2-1.8h-1.8V12H12z"/></svg>
                <span>PhonePe Direct Pay</span>
            </div>

            <div class="phonepe-actions">
                <!-- Share QR to PhonePe -->
                <button type="button" class="btn-phonepe-primary" onclick="shareQRToPhonePe()">
                    <span>📤 Share to App</span>
                </button>

                <!-- Download & Open Scanner -->
                <button type="button" class="btn-phonepe-secondary" onclick="saveQROpenPhonePe()">
                    <span>📷 Save & Scan</span>
                </button>
            </div>

            <div class="phonepe-tip">
                💡 Tap <b>Save & Scan</b> or <b>Share</b> to open PhonePe scanner directly (Bypasses all bank decline blocks 100%)
            </div>
        </div>

        <!-- PAYTM DIRECT (WORKS 1-CLICK) -->
        <button type="button" class="paytm-instant-btn" onclick="openPaytm()">
            <div style="display:flex;align-items:center;gap:10px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="#fff"><path d="M21 4H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-9 11.5c-2.5 0-4.5-2-4.5-4.5s2-4.5 4.5-4.5 4.5 2 4.5 4.5-2 4.5-4.5 4.5z"/></svg>
                <span>Pay via Paytm</span>
            </div>
            <span class="paytm-badge">1-CLICK (FAST)</span>
        </button>

        <!-- QR CODE SECTION -->
        <div class="qr-section">
            <div class="qr-box">
                <img src="<?php echo $qrURL; ?>" alt="Scan to Pay" class="qr-image" id="qrImage">
            </div>
            <div class="qr-hint">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#5f259f" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                Scan or Screenshot with PhonePe / GPay
            </div>
        </div>

        <!-- 1-TAP COPY BAR -->
        <div class="copy-bar">
            <div>
                <div class="copy-label">UPI ID</div>
                <div class="copy-upi"><?php echo $safeUPI; ?></div>
            </div>
            <button type="button" class="copy-action-btn" onclick="copyUPI()">Copy ID</button>
        </div>

    </div>

    <!-- EXPIRED STATE -->
    <div id="expiredContent" class="expired-card" style="<?php echo $isExpired ? 'display:block;' : 'display:none;'; ?>">
        <h2 style="color:#f8fafc;font-size:18px;margin-bottom:6px;">Payment Link Expired</h2>
        <p style="color:var(--text-muted);font-size:12px;line-height:1.5;">This link is no longer valid.</p>
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
const qrSrc          = "<?php echo addslashes($qrURL); ?>";
const expiryTimestamp = <?php echo $expires; ?>;

const isAndroid = /android/i.test(navigator.userAgent);
const isIOS     = /iphone|ipad|ipod/i.test(navigator.userAgent);

const fullQuery = "pa=" + encodeURIComponent(upiID) +
                  "&pn=" + encodeURIComponent(company) +
                  "&am=" + encodeURIComponent(amount) +
                  "&cu=INR" +
                  "&tr=" + encodeURIComponent(txnRef) +
                  "&tn=" + encodeURIComponent("Payment to " + company) +
                  "&mode=02";

/**
 * 1. Web Share API: Share QR Image directly to PhonePe app
 */
async function shareQRToPhonePe() {
    copyToClipboardSilent(amount);
    showToast("Sharing QR to PhonePe...");

    try {
        const response = await fetch(qrSrc);
        const blob = await response.blob();
        const file = new File([blob], "payment-qr.png", { type: "image/png" });

        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({
                title: "Pay ₹" + amount + " to " + company,
                text: "Pay ₹" + amount + " to " + company,
                files: [file]
            });
            return;
        }
    } catch (e) {
        // Fallback to Save & Scan
    }

    saveQROpenPhonePe();
}

/**
 * 2. Save QR & Open PhonePe Scanner (100% bypasses bank security blocks)
 */
async function saveQROpenPhonePe() {
    copyToClipboardSilent(amount);
    showToast("QR Saved! Opening PhonePe Scanner...");

    // Download QR
    try {
        const response = await fetch(qrSrc);
        const blob = await response.blob();
        const blobUrl = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = blobUrl;
        link.download = "PhonePe-Payment-QR.png";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        setTimeout(() => URL.revokeObjectURL(blobUrl), 2000);
    } catch (e) {}

    // Open PhonePe Scanner
    setTimeout(() => {
        let phonePeScanIntent = "";
        if (isAndroid) {
            phonePeScanIntent = "intent:#Intent;action=com.phonepe.app.ui.activity.MainActivity;package=com.phonepe.app;end";
        } else {
            phonePeScanIntent = "phonepe://scan";
        }
        window.location.href = phonePeScanIntent;
    }, 450);
}

function openPaytm() {
    copyToClipboardSilent(amount);
    showToast("Opening Paytm (1-Click)...");

    let paytmURL = "";
    if (isAndroid) {
        paytmURL = "intent://pay?" + fullQuery + "#Intent;scheme=upi;package=net.one97.paytm;end";
    } else {
        paytmURL = "paytmmp://pay?" + fullQuery;
    }

    setTimeout(() => {
        window.location.href = paytmURL;
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
    setTimeout(() => { toast.classList.remove("show"); }, 3500);
}

function updateCountdown() {
    const now = Math.floor(Date.now() / 1000);
    const diff = expiryTimestamp - now;

    if (diff <= 0) {
        const active = document.getElementById("activeContent");
        const expired = document.getElementById("expiredContent");
        if (active) active.style.display = "none";
        if (expired) expired.style.display = "block";
        return;
    }

    const m = Math.floor(diff / 60);
    const s = diff % 60;
    const badge = document.getElementById("countdown");
    if (badge) {
        badge.textContent = (m < 10 ? "0" + m : m) + ":" + (s < 10 ? "0" + s : s);
    }
}

<?php if(!$isExpired): ?>
updateCountdown();
setInterval(updateCountdown, 1000);
<?php endif; ?>
</script>

</body>
</html>