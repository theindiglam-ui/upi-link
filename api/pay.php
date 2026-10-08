<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

/**
 * ============================================================
 * UPI PAYMENT CHECKOUT
 * File: pay.php
 *
 * PREMIUM FINTECH CHECKOUT UI
 *
 * BACKEND:
 * - No database
 * - Receives data from index.php
 * - Base64 JSON decoding
 * - UPI payment URL
 * - QR generation
 * - Expiry countdown
 * - UPI app buttons
 *
 * IMAGE FILES:
 * /assets/images/phonepe.png
 * /assets/images/googlepay.png
 * /assets/images/paytm.png
 * ============================================================
 */


/* ============================================================
   ERROR FUNCTION
============================================================ */

function showError($message)
{
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<meta name="theme-color" content="#0f766e">

<title>Payment Link</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

html,
body{
    width:100%;
    min-height:100%;
}

body{
    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:#f3f7f6;
    color:#17201e;

    min-height:100vh;

    display:flex;
    align-items:center;
    justify-content:center;

    padding:20px;
}

.error-card{

    width:100%;
    max-width:420px;

    background:#ffffff;

    border:1px solid #dfe9e6;

    border-radius:20px;

    padding:42px 25px;

    text-align:center;

    box-shadow:
        0 20px 50px
        rgba(15,118,110,.08);

    animation:
        errorIn .45s ease both;
}

.error-icon{

    width:68px;
    height:68px;

    margin:0 auto 18px;

    border-radius:50%;

    background:#fff1f2;

    color:#dc2626;

    border:1px solid #fecdd3;

    display:flex;

    align-items:center;
    justify-content:center;

    font-size:28px;
    font-weight:900;
}

.error-title{

    font-size:21px;

    font-weight:850;

    color:#17201e;

    margin-bottom:8px;
}

.error-text{

    color:#6b7a76;

    font-size:13px;

    line-height:1.6;
}

@keyframes errorIn{

    from{
        opacity:0;
        transform:translateY(18px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }
}

</style>

</head>

<body>

<div class="error-card">

    <div class="error-icon">
        !
    </div>

    <div class="error-title">
        Payment Link Invalid
    </div>

    <div class="error-text">

        <?php
        echo htmlspecialchars(
            $message,
            ENT_QUOTES,
            'UTF-8'
        );
        ?>

    </div>

</div>

</body>
</html>

<?php
exit;
}


/* ============================================================
   GET DATA
============================================================ */

$data = isset($_GET['data'])
    ? trim($_GET['data'])
    : '';

if($data === ''){

    showError(
        "Invalid payment link."
    );

}


/* ============================================================
   BASE64 DECODE
============================================================ */

$decodedBase64 = base64_decode(
    $data,
    true
);


/*
 * Fallback if + was converted to space.
 */

if($decodedBase64 === false){

    $decodedBase64 = base64_decode(
        str_replace(
            ' ',
            '+',
            $data
        ),
        true
    );

}


if($decodedBase64 === false){

    showError(
        "Unable to decode payment link."
    );

}


/* ============================================================
   JSON DATA
============================================================ */

/*
 * index.php creates Base64 from UTF-8 JSON.
 *
 * Do NOT rawurldecode() decoded JSON.
 */

$json = $decodedBase64;

$payment = json_decode(
    $json,
    true
);

if(
    !is_array($payment) ||
    json_last_error() !== JSON_ERROR_NONE
){

    showError(
        "Invalid payment data."
    );

}


/* ============================================================
   PAYMENT DATA
============================================================ */

$company = trim(
    (string)(
        $payment['company'] ?? ''
    )
);

$logo = trim(
    (string)(
        $payment['logo'] ?? ''
    )
);

$upi = trim(
    (string)(
        $payment['upi'] ?? ''
    )
);

$amount = trim(
    (string)(
        $payment['amount'] ?? ''
    )
);

$expires = (int)(
    $payment['expires'] ?? 0
);


/* ============================================================
   VALIDATION
============================================================ */

if(
    $company === '' ||
    $upi === '' ||
    $amount === '' ||
    $expires <= 0
){

    showError(
        "Payment link data is incomplete."
    );

}


/* ============================================================
   AMOUNT VALIDATION
============================================================ */

if(
    !is_numeric($amount) ||
    (float)$amount <= 0
){

    showError(
        "Invalid payment amount."
    );

}


/* ============================================================
   UPI VALIDATION
============================================================ */

if(
    strpos(
        $upi,
        '@'
    ) === false
){

    showError(
        "Invalid UPI ID."
    );

}


/* ============================================================
   EXPIRY
============================================================ */

$currentTime = time();

$isExpired = (
    $currentTime >= $expires
);


/* ============================================================
   SAFE VALUES
============================================================ */

$safeCompany = htmlspecialchars(
    $company,
    ENT_QUOTES,
    'UTF-8'
);

$safeUPI = htmlspecialchars(
    $upi,
    ENT_QUOTES,
    'UTF-8'
);

$safeLogo = htmlspecialchars(
    $logo,
    ENT_QUOTES,
    'UTF-8'
);

$displayAmount = number_format(
    (float)$amount,
    2,
    '.',
    ''
);


/* ============================================================
   UPI PAYMENT URL
============================================================ */

$upiPaymentURL =
    'upi://pay' .
    '?pa=' .
    rawurlencode($upi) .
    '&pn=' .
    rawurlencode($company) .
    '&am=' .
    rawurlencode($displayAmount) .
    '&cu=INR';


/* ============================================================
   QR CODE URL
============================================================ */

$qrURL =
    'https://api.qrserver.com/v1/create-qr-code/' .
    '?size=400x400' .
    '&margin=12' .
    '&data=' .
    rawurlencode(
        $upiPaymentURL
    );


/* ============================================================
   APP IMAGE PATHS
============================================================ */

$phonePeImage =
    'assets/images/phonepe.png';

$googlePayImage =
    'assets/images/googlepay.png';

$paytmImage =
    'assets/images/paytm.png';


$phonePeExists = file_exists(
    __DIR__ .
    '/assets/images/phonepe.png'
);

$googlePayExists = file_exists(
    __DIR__ .
    '/assets/images/googlepay.png'
);

$paytmExists = file_exists(
    __DIR__ .
    '/assets/images/paytm.png'
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<meta
    name="theme-color"
    content="#0f766e"
>

<meta
    name="color-scheme"
    content="light"
>

<title>
    <?php echo $safeCompany; ?> - Secure Payment
</title>


<style>

/* ============================================================
   RESET
============================================================ */

*{

    margin:0;
    padding:0;

    box-sizing:border-box;

}

html,
body{

    width:100%;
    min-height:100%;

}

button,
input{

    font-family:inherit;

}

button{

    -webkit-tap-highlight-color:transparent;

}


/* ============================================================
   BODY
============================================================ */

body{

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:#f2f7f6;

    color:#17201e;

    -webkit-font-smoothing:antialiased;

    overflow-x:hidden;

}


/* ============================================================
   PAGE BACKGROUND
============================================================ */

.checkout-page{

    min-height:100vh;

    width:100%;

    display:flex;

    justify-content:center;

    align-items:flex-start;

    padding:40px 20px 60px;

    background:

        radial-gradient(
            circle at 50% 0%,
            #ffffff 0%,
            #f5faf9 42%,
            #edf4f2 100%
        );

}


/* ============================================================
   MAIN CHECKOUT
============================================================ */

.checkout-wrapper{

    width:100%;

    max-width:540px;

    background:#ffffff;

    border:
        1px solid
        #dce8e5;

    border-radius:22px;

    overflow:hidden;

    box-shadow:

        0 24px 70px
        rgba(15,118,110,.08),

        0 5px 20px
        rgba(15,23,42,.04);

    animation:

        checkoutEnter
        .55s
        cubic-bezier(.2,.7,.2,1)
        both;

}


@keyframes checkoutEnter{

    from{

        opacity:0;

        transform:
            translateY(25px)
            scale(.985);

    }

    to{

        opacity:1;

        transform:
            translateY(0)
            scale(1);

    }

}


/* ============================================================
   HEADER
============================================================ */

.checkout-header{

    height:65px;

    display:flex;

    align-items:center;

    justify-content:center;

    position:relative;

    border-bottom:
        1px solid
        #e9f0ee;

    background:#ffffff;

}


.checkout-header:after{

    content:"";

    position:absolute;

    left:0;
    right:0;
    bottom:0;

    height:2px;

    background:
        linear-gradient(
            90deg,
            transparent,
            #0f766e,
            transparent
        );

    opacity:.35;

}


.secure-label{

    display:flex;

    align-items:center;

    gap:8px;

    color:#53645f;

    font-size:11px;

    font-weight:750;

}


.secure-check{

    width:22px;

    height:22px;

    border-radius:50%;

    background:#e7f7f3;

    color:#0f766e;

    border:
        1px solid
        #c7e9e1;

    display:flex;

    align-items:center;

    justify-content:center;

    font-size:11px;

    font-weight:900;

}


/* ============================================================
   MERCHANT
============================================================ */

.merchant{

    text-align:center;

    padding:
        31px
        22px
        14px;

}


.merchant-logo{

    width:74px;

    height:74px;

    display:block;

    margin:
        0
        auto
        14px;

    padding:8px;

    object-fit:contain;

    border:
        1px solid
        #dce8e5;

    border-radius:17px;

    background:#ffffff;

    box-shadow:
        0 8px 25px
        rgba(15,118,110,.08);

    animation:
        logoEnter
        .55s
        ease
        .1s
        both;

}


@keyframes logoEnter{

    from{

        opacity:0;

        transform:
            scale(.78)
            rotate(-3deg);

    }

    to{

        opacity:1;

        transform:
            scale(1)
            rotate(0);

    }

}


.merchant-name{

    color:#17201e;

    font-size:19px;

    font-weight:850;

    line-height:1.3;

    word-break:break-word;

}


.merchant-subtitle{

    margin-top:6px;

    color:#7b8a86;

    font-size:11px;

    font-weight:500;

}


/* ============================================================
   AMOUNT
============================================================ */

.amount-area{

    text-align:center;

    padding:
        4px
        20px
        24px;

}


.amount-label{

    color:#7c8a87;

    font-size:11px;

    font-weight:600;

    margin-bottom:6px;

}


.amount{

    color:#0f766e;

    font-size:39px;

    line-height:1.08;

    font-weight:900;

    letter-spacing:-1.5px;

    animation:
        amountIn
        .5s
        ease
        .15s
        both;

}


@keyframes amountIn{

    from{

        opacity:0;

        transform:
            translateY(8px);

    }

    to{

        opacity:1;

        transform:
            translateY(0);

    }

}


/* ============================================================
   EXPIRY
============================================================ */

.expiry{

    margin:
        0
        24px
        22px;

    padding:
        14px
        15px;

    border:
        1px solid
        #f1dfbb;

    border-radius:13px;

    background:
        linear-gradient(
            135deg,
            #fffaf0,
            #fffdf8
        );

    text-align:center;

    position:relative;

    overflow:hidden;

}


.expiry:before{

    content:"";

    position:absolute;

    left:0;
    top:0;

    width:100%;
    height:2px;

    background:#d97706;

    opacity:.55;

}


.expiry-label{

    color:#987033;

    font-size:9px;

    font-weight:850;

    letter-spacing:.7px;

    text-transform:uppercase;

    margin-bottom:5px;

}


.countdown{

    color:#b45309;

    font-size:18px;

    font-weight:900;

    letter-spacing:.5px;

}


.expiry-date{

    color:#a19070;

    font-size:9px;

    margin-top:5px;

}


/* ============================================================
   CONTENT
============================================================ */

.payment-content{

    padding:
        0
        24px
        30px;

}


/* ============================================================
   SECTION
============================================================ */

.section{

    margin-top:19px;

}


.section-title{

    color:#26332f;

    font-size:12px;

    font-weight:850;

    margin-bottom:9px;

}


/* ============================================================
   UPI BOX
============================================================ */

.upi-box{

    width:100%;

    min-height:52px;

    display:flex;

    align-items:center;

    border:
        1px solid
        #d7e4e0;

    border-radius:11px;

    overflow:hidden;

    background:#fbfdfc;

    transition:

        border-color .2s ease,

        box-shadow .2s ease;
}


.upi-box:hover{

    border-color:#9dcfc4;

    box-shadow:
        0 7px 20px
        rgba(15,118,110,.06);

}


.upi-value{

    flex:1;

    min-width:0;

    padding:
        0
        14px;

    color:#35433f;

    font-size:12px;

    font-weight:700;

    overflow:hidden;

    text-overflow:ellipsis;

    white-space:nowrap;

}


.copy-button{

    height:52px;

    padding:
        0
        18px;

    border:0;

    border-left:
        1px solid
        #dfeae7;

    background:#f1faf7;

    color:#0f766e;

    font-size:10px;

    font-weight:900;

    cursor:pointer;

    transition:

        background .2s ease,

        color .2s ease;
}


.copy-button:hover{

    background:#e5f7f2;

    color:#115e59;

}


.copy-button:active{

    background:#d8f0ea;

}


/* ============================================================
   QR SECTION
============================================================ */

.qr-section{

    text-align:center;

    border:
        1px solid
        #dce8e5;

    border-radius:16px;

    padding:
        22px
        15px
        18px;

    background:

        linear-gradient(
            180deg,
            #ffffff 0%,
            #fafdfe 100%
        );

    box-shadow:
        0 7px 24px
        rgba(15,118,110,.035);

}


.qr-title{

    color:#17201e;

    font-size:15px;

    font-weight:850;

}


.qr-subtitle{

    color:#7a8985;

    font-size:10px;

    margin-top:5px;

    margin-bottom:16px;

}


.qr-loader{

    width:214px;

    height:214px;

    margin:0 auto;

    display:flex;

    align-items:center;

    justify-content:center;

    border:
        1px solid
        #dfeae7;

    border-radius:13px;

    background:#f8fbfa;

}


.qr-spinner{

    width:32px;

    height:32px;

    border:
        3px solid
        #dce9e6;

    border-top-color:#0f766e;

    border-radius:50%;

    animation:
        spin
        .7s
        linear
        infinite;

}


.qr-image{

    display:none;

    width:214px;

    height:214px;

    margin:0 auto;

    padding:7px;

    object-fit:contain;

    background:#ffffff;

    border:
        1px solid
        #dce8e5;

    border-radius:13px;

    box-shadow:
        0 8px 25px
        rgba(15,118,110,.07);

    animation:
        qrAppear
        .35s
        ease
        both;

}


@keyframes qrAppear{

    from{

        opacity:0;

        transform:
            scale(.94);

    }

    to{

        opacity:1;

        transform:
            scale(1);

    }

}


.qr-download{

    width:100%;

    height:45px;

    margin-top:13px;

    border:
        1px solid
        #cfe0dc;

    border-radius:10px;

    background:#ffffff;

    color:#31514a;

    font-size:10px;

    font-weight:850;

    cursor:pointer;

    transition:

        background .2s ease,

        border-color .2s ease,

        color .2s ease,

        transform .15s ease;
}


.qr-download:hover{

    background:#effaf7;

    border-color:#a5d2c8;

    color:#0f766e;

}


.qr-download:active{

    transform:scale(.98);

}


/* ============================================================
   MAIN PAYMENT BUTTON
============================================================ */

.pay-button{

    position:relative;

    width:100%;

    height:55px;

    margin-top:18px;

    border:0;

    border-radius:11px;

    background:#0f766e;

    color:#ffffff;

    font-size:14px;

    font-weight:900;

    cursor:pointer;

    overflow:hidden;

    box-shadow:
        0 9px 24px
        rgba(15,118,110,.20);

    transition:

        transform .15s ease,

        background .2s ease,

        box-shadow .2s ease;

}


.pay-button:before{

    content:"";

    position:absolute;

    top:0;
    left:-110%;

    width:75%;
    height:100%;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(255,255,255,.18),
            transparent
        );

    transform:skewX(-20deg);

    transition:left .6s ease;

}


.pay-button:hover{

    background:#115e59;

    box-shadow:
        0 12px 28px
        rgba(15,118,110,.26);

}


.pay-button:hover:before{

    left:135%;

}


.pay-button:active{

    transform:scale(.985);

}


.pay-button.loading{

    pointer-events:none;

    background:#115e59;

}


.pay-text{

    transition:
        opacity .2s ease;

}


.pay-button.loading
.pay-text{

    opacity:0;

}


.pay-spinner{

    position:absolute;

    width:22px;
    height:22px;

    left:50%;
    top:50%;

    margin:
        -11px
        0
        0
        -11px;

    border:
        3px solid
        rgba(255,255,255,.30);

    border-top-color:#ffffff;

    border-radius:50%;

    display:none;

    animation:
        spin
        .7s
        linear
        infinite;

}


.pay-button.loading
.pay-spinner{

    display:block;

}


/* ============================================================
   UPI APP HEADING
============================================================ */

.apps-heading{

    text-align:center;

    color:#879590;

    font-size:10px;

    font-weight:650;

    margin:
        23px
        0
        12px;

    position:relative;

}


.apps-heading:before,
.apps-heading:after{

    content:"";

    position:absolute;

    top:50%;

    width:27%;

    height:1px;

    background:#e4ece9;

}


.apps-heading:before{

    left:0;

}


.apps-heading:after{

    right:0;

}


/* ============================================================
   UPI APPS
============================================================ */

.apps{

    display:grid;

    grid-template-columns:
        repeat(3,1fr);

    gap:9px;

}


.app-button{

    height:68px;

    border:
        1px solid
        #dce7e4;

    border-radius:13px;

    background:#ffffff;

    display:flex;

    align-items:center;

    justify-content:center;

    cursor:pointer;

    position:relative;

    overflow:hidden;

    transition:

        transform .18s ease,

        background .18s ease,

        border-color .18s ease,

        box-shadow .18s ease;

}


.app-button:hover{

    background:#fbfdfc;

    border-color:#b8d6cf;

    box-shadow:
        0 7px 18px
        rgba(15,118,110,.07);

    transform:translateY(-2px);

}


.app-button:active{

    transform:scale(.95);

}


.app-button img{

    display:block;

    max-width:82px;

    max-height:39px;

    width:auto;

    height:auto;

    object-fit:contain;

}


.app-text{

    color:#35433f;

    font-size:10px;

    font-weight:850;

}


/* ============================================================
   PAYMENT DETAILS
============================================================ */

.details{

    margin-top:24px;

    border:
        1px solid
        #e1ebe8;

    border-radius:13px;

    overflow:hidden;

    background:#fbfdfc;

}


.detail-row{

    min-height:45px;

    display:flex;

    align-items:center;

    justify-content:space-between;

    gap:15px;

    padding:
        0
        13px;

    border-bottom:
        1px solid
        #eaf0ee;

    font-size:11px;

}


.detail-row:last-child{

    border-bottom:0;

}


.detail-label{

    color:#7c8a86;

    font-weight:500;

}


.detail-value{

    color:#26332f;

    font-weight:750;

    text-align:right;

    word-break:break-word;

}


/* ============================================================
   FOOTER
============================================================ */

.checkout-footer{

    padding:
        18px
        20px;

    text-align:center;

    border-top:
        1px solid
        #e7efed;

    background:#fbfdfc;

    color:#9aa7a3;

    font-size:9px;

}


.footer-secure{

    display:flex;

    justify-content:center;

    align-items:center;

    gap:6px;

    margin-bottom:6px;

    color:#687874;

    font-weight:650;

}


.footer-check{

    width:16px;
    height:16px;

    border-radius:50%;

    display:flex;

    align-items:center;
    justify-content:center;

    background:#e7f7f3;

    color:#0f766e;

    border:
        1px solid
        #c7e9e1;

    font-size:8px;

    font-weight:900;

}


/* ============================================================
   COPY TOAST
============================================================ */

.copy-toast{

    position:fixed;

    left:50%;
    bottom:25px;

    z-index:5000;

    padding:
        11px
        17px;

    border-radius:10px;

    background:#17201e;

    color:#ffffff;

    font-size:10px;

    font-weight:750;

    box-shadow:
        0 10px 28px
        rgba(0,0,0,.16);

    opacity:0;

    pointer-events:none;

    transform:
        translate(-50%,15px);

    transition:

        opacity .25s ease,

        transform .25s ease;

}


.copy-toast.show{

    opacity:1;

    transform:
        translate(-50%,0);

}


/* ============================================================
   FULL SCREEN LOADING
============================================================ */

.loading-overlay{

    position:fixed;

    inset:0;

    z-index:9999;

    background:
        rgba(255,255,255,.97);

    backdrop-filter:
        blur(6px);

    display:flex;

    align-items:center;

    justify-content:center;

    flex-direction:column;

    opacity:0;

    visibility:hidden;

    transition:.2s ease;

}


.loading-overlay.active{

    opacity:1;

    visibility:visible;

}


.loading-spinner{

    width:53px;

    height:53px;

    border:
        4px solid
        #dce9e6;

    border-top-color:#0f766e;

    border-radius:50%;

    animation:
        spin
        .7s
        linear
        infinite;

}


.loading-title{

    margin-top:18px;

    color:#17201e;

    font-size:14px;

    font-weight:850;

}


.loading-subtitle{

    margin-top:6px;

    color:#7b8a86;

    font-size:10px;

}


/* ============================================================
   EXPIRED
============================================================ */

.expired-screen{

    text-align:center;

    padding:
        62px
        25px;

    animation:
        fadeIn
        .4s
        ease
        both;

}


.expired-icon{

    width:74px;

    height:74px;

    margin:
        0
        auto
        19px;

    border-radius:50%;

    background:#fff1f2;

    color:#dc2626;

    border:
        1px solid
        #fecdd3;

    display:flex;

    align-items:center;

    justify-content:center;

    font-size:29px;

    font-weight:900;

    box-shadow:
        0 8px 24px
        rgba(220,38,38,.07);

}


.expired-title{

    color:#17201e;

    font-size:22px;

    font-weight:850;

}


.expired-text{

    margin-top:9px;

    color:#778581;

    font-size:12px;

    line-height:1.65;

}


.expired-company{

    margin-top:18px;

    color:#0f766e;

    font-size:13px;

    font-weight:850;

}


/* ============================================================
   ANIMATIONS
============================================================ */

@keyframes spin{

    from{
        transform:rotate(0deg);
    }

    to{
        transform:rotate(360deg);
    }

}


@keyframes fadeIn{

    from{
        opacity:0;
    }

    to{
        opacity:1;
    }

}


/* ============================================================
   MOBILE
============================================================ */

@media(max-width:600px){

    body{

        background:#ffffff;

    }


    .checkout-page{

        padding:0;

        min-height:100vh;

        display:block;

        background:#ffffff;

    }


    .checkout-wrapper{

        min-height:100vh;

        max-width:none;

        border:0;

        border-radius:0;

        box-shadow:none;

    }


    .checkout-header{

        height:57px;

    }


    .merchant{

        padding-top:27px;

    }


    .merchant-logo{

        width:69px;
        height:69px;

        border-radius:16px;

    }


    .merchant-name{

        font-size:18px;

    }


    .amount{

        font-size:33px;

    }


    .payment-content{

        padding:
            0
            17px
            28px;

    }


    .expiry{

        margin-left:17px;

        margin-right:17px;

    }


    .qr-section{

        padding:
            20px
            13px
            17px;

    }


    .qr-image,
    .qr-loader{

        width:200px;

        height:200px;

    }


    .apps{

        gap:7px;

    }


    .app-button{

        height:62px;

    }


    .app-button img{

        max-width:73px;

        max-height:36px;

    }


    .pay-button{

        height:55px;

    }

}


/* ============================================================
   SMALL MOBILE
============================================================ */

@media(max-width:350px){

    .amount{

        font-size:30px;

    }


    .app-button img{

        max-width:63px;

    }


    .payment-content{

        padding-left:13px;

        padding-right:13px;

    }


    .expiry{

        margin-left:13px;

        margin-right:13px;

    }


    .qr-image,
    .qr-loader{

        width:190px;

        height:190px;

    }

}

</style>

</head>


<body>


<!-- ============================================================
     FULL SCREEN LOADING
============================================================ -->

<div
    class="loading-overlay"
    id="loadingOverlay"
>

    <div class="loading-spinner"></div>

    <div
        class="loading-title"
        id="loadingTitle"
    >
        Opening UPI App
    </div>

    <div class="loading-subtitle">
        Please wait...
    </div>

</div>


<!-- ============================================================
     COPY TOAST
============================================================ -->

<div
    class="copy-toast"
    id="copyToast"
>

    UPI ID copied successfully

</div>


<!-- ============================================================
     CHECKOUT PAGE
============================================================ -->

<div class="checkout-page">


<div class="checkout-wrapper">


<!-- ============================================================
     HEADER
============================================================ -->

<div class="checkout-header">

    <div class="secure-label">

        <span class="secure-check">
            ✓
        </span>

        Secure UPI Payment

    </div>

</div>


<!-- ============================================================
     ACTIVE PAYMENT
============================================================ -->

<div
    id="activePayment"
    style="<?php echo $isExpired ? 'display:none;' : ''; ?>"
>


<!-- ============================================================
     MERCHANT
============================================================ -->

<div class="merchant">


<?php if($logo): ?>

<img
    src="<?php echo $safeLogo; ?>"
    class="merchant-logo"
    alt="<?php echo $safeCompany; ?>"
    onerror="this.style.display='none';"
>

<?php endif; ?>


<div class="merchant-name">

    <?php echo $safeCompany; ?>

</div>


<div class="merchant-subtitle">

    Payment Request

</div>


</div>


<!-- ============================================================
     AMOUNT
============================================================ -->

<div class="amount-area">

    <div class="amount-label">

        Amount to Pay

    </div>


    <div class="amount">

        ₹<?php echo $displayAmount; ?>

    </div>

</div>


<!-- ============================================================
     EXPIRY
============================================================ -->

<div class="expiry">

    <div class="expiry-label">

        Payment link expires in

    </div>


    <div
        class="countdown"
        id="countdown"
    >

        Loading...

    </div>


    <div class="expiry-date">

        Expires:
        <?php

        echo date(
            'd M Y, h:i A',
            $expires
        );

        ?>

    </div>

</div>


<!-- ============================================================
     CONTENT
============================================================ -->

<div class="payment-content">


<!-- ============================================================
     UPI ID
============================================================ -->

<div class="section">

    <div class="section-title">

        UPI ID

    </div>


    <div class="upi-box">

        <div class="upi-value">

            <?php echo $safeUPI; ?>

        </div>


        <button
            type="button"
            class="copy-button"
            onclick="copyUPI()"
        >

            COPY

        </button>

    </div>

</div>


<!-- ============================================================
     QR
============================================================ -->

<div class="section">

<div class="qr-section">

    <div class="qr-title">

        Scan & Pay

    </div>


    <div class="qr-subtitle">

        Scan this QR code using any UPI app

    </div>


    <div
        class="qr-loader"
        id="qrLoader"
    >

        <div class="qr-spinner"></div>

    </div>


    <img
        src="<?php echo htmlspecialchars(
            $qrURL,
            ENT_QUOTES,
            'UTF-8'
        ); ?>"
        class="qr-image"
        id="qrImage"
        alt="UPI QR Code"
        onload="qrLoaded()"
        onerror="qrError()"
    >


    <button
        type="button"
        class="qr-download"
        onclick="downloadQR()"
    >

        DOWNLOAD QR CODE

    </button>

</div>

</div>


<!-- ============================================================
     MAIN PAYMENT BUTTON
============================================================ -->

<button
    type="button"
    class="pay-button"
    id="payButton"
    onclick="openUPI()"
>

    <span class="pay-text">

        PAY ₹<?php echo $displayAmount; ?>

    </span>


    <span class="pay-spinner"></span>

</button>


<!-- ============================================================
     UPI APPS
============================================================ -->

<div class="apps-heading">

    OR PAY USING UPI APP

</div>


<div class="apps">


<!-- PHONEPE -->

<button
    type="button"
    class="app-button"
    onclick="openUPI('PhonePe')"
>

<?php if($phonePeExists): ?>

    <img
        src="<?php echo $phonePeImage; ?>"
        alt="PhonePe"
    >

<?php else: ?>

    <span class="app-text">
        PHONEPE
    </span>

<?php endif; ?>

</button>


<!-- GOOGLE PAY -->

<button
    type="button"
    class="app-button"
    onclick="openUPI('Google Pay')"
>

<?php if($googlePayExists): ?>

    <img
        src="<?php echo $googlePayImage; ?>"
        alt="Google Pay"
    >

<?php else: ?>

    <span class="app-text">
        GOOGLE PAY
    </span>

<?php endif; ?>

</button>


<!-- PAYTM -->

<button
    type="button"
    class="app-button"
    onclick="openUPI('Paytm')"
>

<?php if($paytmExists): ?>

    <img
        src="<?php echo $paytmImage; ?>"
        alt="Paytm"
    >

<?php else: ?>

    <span class="app-text">
        PAYTM
    </span>

<?php endif; ?>

</button>


</div>


<!-- ============================================================
     PAYMENT DETAILS
============================================================ -->

<div class="details">


<div class="detail-row">

    <span class="detail-label">
        Merchant
    </span>

    <span class="detail-value">
        <?php echo $safeCompany; ?>
    </span>

</div>


<div class="detail-row">

    <span class="detail-label">
        UPI ID
    </span>

    <span class="detail-value">
        <?php echo $safeUPI; ?>
    </span>

</div>


<div class="detail-row">

    <span class="detail-label">
        Amount
    </span>

    <span class="detail-value">
        ₹<?php echo $displayAmount; ?>
    </span>

</div>


<div class="detail-row">

    <span class="detail-label">
        Currency
    </span>

    <span class="detail-value">
        INR
    </span>

</div>


</div>


</div>


<!-- ============================================================
     FOOTER
============================================================ -->

<div class="checkout-footer">

    <div class="footer-secure">

        <span class="footer-check">
            ✓
        </span>

        Secure payment through UPI

    </div>


    Powered by
    <?php echo $safeCompany; ?>

</div>


</div>


<!-- ============================================================
     EXPIRED PAYMENT
============================================================ -->

<div
    class="expired-screen"
    id="expiredScreen"
    style="<?php echo $isExpired ? 'display:block;' : 'display:none;'; ?>"
>

    <div class="expired-icon">
        !
    </div>


    <div class="expired-title">

        Payment Link Expired

    </div>


    <div class="expired-text">

        This payment link is no longer active.

        Please request a new payment link
        from the merchant.

    </div>


    <div class="expired-company">

        <?php echo $safeCompany; ?>

    </div>

</div>


</div>


</div>


<script>

/* ============================================================
   PAYMENT DATA
============================================================ */

const upiID =
<?php

echo json_encode(
    $upi,
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);

?>;


const companyName =
<?php

echo json_encode(
    $company,
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);

?>;


const paymentAmount =
<?php

echo json_encode(
    $displayAmount
);

?>;


const expiryTimestamp =
<?php

echo $expires;

?>;


/* ============================================================
   UPI PAYMENT URL
============================================================ */

const upiPaymentURL =
    "upi://pay" +
    "?pa=" +
    encodeURIComponent(upiID) +
    "&pn=" +
    encodeURIComponent(companyName) +
    "&am=" +
    encodeURIComponent(paymentAmount) +
    "&cu=INR";


/* ============================================================
   OPEN UPI
============================================================ */

function openUPI(appName){

    if(
        Math.floor(
            Date.now() / 1000
        ) >= expiryTimestamp
    ){

        expirePayment();

        return;

    }


    const button =
        document.getElementById(
            "payButton"
        );


    const overlay =
        document.getElementById(
            "loadingOverlay"
        );


    const title =
        document.getElementById(
            "loadingTitle"
        );


    if(appName){

        title.textContent =
            "Opening " +
            appName;

    }else{

        title.textContent =
            "Opening UPI App";

    }


    overlay.classList.add(
        "active"
    );


    if(button){

        button.classList.add(
            "loading"
        );

    }


    /*
     * Small delay for loading animation.
     */

    setTimeout(
        function(){

            window.location.href =
                upiPaymentURL;

        },
        450
    );

}


/* ============================================================
   COPY UPI
============================================================ */

async function copyUPI(){

    try{

        if(
            navigator.clipboard &&
            window.isSecureContext
        ){

            await navigator.clipboard.writeText(
                upiID
            );

        }else{

            const temp =
                document.createElement(
                    "input"
                );


            temp.value =
                upiID;


            temp.style.position =
                "fixed";


            temp.style.left =
                "-9999px";


            document.body.appendChild(
                temp
            );


            temp.select();


            document.execCommand(
                "copy"
            );


            temp.remove();

        }


        showCopyToast();

    }catch(error){

        alert(
            "UPI ID: " +
            upiID
        );

    }

}


/* ============================================================
   COPY TOAST
============================================================ */

function showCopyToast(){

    const toast =
        document.getElementById(
            "copyToast"
        );


    toast.classList.add(
        "show"
    );


    setTimeout(
        function(){

            toast.classList.remove(
                "show"
            );

        },
        2000
    );

}


/* ============================================================
   QR LOADED
============================================================ */

function qrLoaded(){

    const loader =
        document.getElementById(
            "qrLoader"
        );


    const image =
        document.getElementById(
            "qrImage"
        );


    if(loader){

        loader.style.display =
            "none";

    }


    if(image){

        image.style.display =
            "block";

    }

}


/* ============================================================
   QR ERROR
============================================================ */

function qrError(){

    const loader =
        document.getElementById(
            "qrLoader"
        );


    if(loader){

        loader.innerHTML =
            '<div style="' +
            'font-size:10px;' +
            'color:#6b7a76;' +
            'padding:20px;' +
            'line-height:1.5;' +
            '">' +
            'QR code could not be loaded.' +
            '</div>';

    }

}


/* ============================================================
   COUNTDOWN
============================================================ */

function updateCountdown(){

    const now =
        Math.floor(
            Date.now() / 1000
        );


    let remaining =
        expiryTimestamp -
        now;


    if(
        remaining <= 0
    ){

        expirePayment();

        return;

    }


    const days =
        Math.floor(
            remaining / 86400
        );


    remaining %=
        86400;


    const hours =
        Math.floor(
            remaining / 3600
        );


    remaining %=
        3600;


    const minutes =
        Math.floor(
            remaining / 60
        );


    const seconds =
        remaining %
        60;


    let text = "";


    if(days > 0){

        text +=
            days +
            "d ";

    }


    text +=
        String(hours)
            .padStart(2,"0") +
        ":";


    text +=
        String(minutes)
            .padStart(2,"0") +
        ":";


    text +=
        String(seconds)
            .padStart(2,"0");


    const countdown =
        document.getElementById(
            "countdown"
        );


    if(countdown){

        countdown.textContent =
            text;

    }

}


/* ============================================================
   EXPIRE PAYMENT
============================================================ */

function expirePayment(){

    const active =
        document.getElementById(
            "activePayment"
        );


    const expired =
        document.getElementById(
            "expiredScreen"
        );


    const overlay =
        document.getElementById(
            "loadingOverlay"
        );


    if(active){

        active.style.display =
            "none";

    }


    if(expired){

        expired.style.display =
            "block";

    }


    if(overlay){

        overlay.classList.remove(
            "active"
        );

    }

}


/* ============================================================
   DOWNLOAD QR
============================================================ */

async function downloadQR(){

    const qr =
        document.getElementById(
            "qrImage"
        );


    if(
        !qr ||
        qr.style.display === "none"
    ){

        return;

    }


    try{

        const response =
            await fetch(
                qr.src
            );


        const blob =
            await response.blob();


        const blobURL =
            URL.createObjectURL(
                blob
            );


        const link =
            document.createElement(
                "a"
            );


        link.href =
            blobURL;


        link.download =
            "upi-payment-qr.png";


        document.body.appendChild(
            link
        );


        link.click();


        link.remove();


        URL.revokeObjectURL(
            blobURL
        );

    }catch(error){

        window.open(
            qr.src,
            "_blank"
        );

    }

}


/* ============================================================
   START COUNTDOWN
============================================================ */

<?php if(!$isExpired): ?>

updateCountdown();

setInterval(
    updateCountdown,
    1000
);

<?php endif; ?>

</script>


</body>

</html>