<?php
/**
 * ============================================================
 * UPI PAYMENT LINK CREATOR
 * File: index.php
 *
 * FINAL VERSION
 *
 * FEATURES:
 * - Company Name
 * - Logo URL / Path
 * - UPI ID
 * - Payment Amount
 * - Custom Expiry
 * - Generate Payment Link
 * - Copy Payment Link
 * - Open Payment Page
 *
 * IMPORTANT:
 * - NO local image upload
 * - NO Base64 logo
 * - Logo is always URL / Path
 * - No database required
 * ============================================================
 */

$defaultCompanyName = "ZXWEB";
$defaultLogo = "logo.png";
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    <?php
    echo htmlspecialchars(
        $defaultCompanyName,
        ENT_QUOTES,
        'UTF-8'
    );
    ?>
    - Payment Link
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


/* ============================================================
   BODY
============================================================ */

body{

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:#f3f7f5;

    color:#17201d;

    min-height:100vh;

    padding:25px 15px;
}


/* ============================================================
   CONTAINER
============================================================ */

.container{

    width:100%;

    max-width:480px;

    margin:0 auto;
}


/* ============================================================
   CARD
============================================================ */

.card{

    background:#ffffff;

    border-radius:26px;

    padding:28px;

    box-shadow:
        0 15px 45px rgba(0,0,0,.08);
}


/* ============================================================
   COMPANY HEADER
============================================================ */

.company-header{

    text-align:center;

    margin-bottom:25px;
}


.company-logo{

    width:72px;

    height:72px;

    object-fit:contain;

    display:block;

    margin:0 auto 11px;

    padding:7px;

    border-radius:50%;

    background:#ffffff;

    border:1px solid #dfe9e5;

    box-shadow:
        0 5px 18px rgba(0,0,0,.07);
}


.company-name{

    font-size:22px;

    font-weight:800;

    color:#17201d;

    letter-spacing:.2px;

    word-break:break-word;
}


/* ============================================================
   TITLE
============================================================ */

.page-title{

    text-align:center;

    font-size:25px;

    font-weight:800;

    margin-bottom:7px;
}


.page-subtitle{

    text-align:center;

    font-size:13px;

    line-height:1.5;

    color:#75827d;

    margin-bottom:25px;
}


/* ============================================================
   FORM
============================================================ */

.form-group{

    margin-bottom:17px;
}


.form-label{

    display:block;

    font-size:13px;

    font-weight:700;

    color:#34423e;

    margin-bottom:7px;
}


.input-wrap{

    position:relative;
}


.form-input{

    width:100%;

    height:52px;

    border:1px solid #dce6e2;

    border-radius:13px;

    background:#fafcfc;

    padding:0 15px;

    outline:none;

    color:#17201d;

    font-size:15px;

    transition:.2s;
}


.form-input:focus{

    border-color:#2e9f7a;

    background:#ffffff;

    box-shadow:
        0 0 0 3px rgba(46,159,122,.10);
}


/* ============================================================
   LOGO URL
============================================================ */

.logo-url-help{

    margin-top:7px;

    color:#87928e;

    font-size:11px;

    line-height:1.5;
}


.logo-preview-box{

    display:none;

    margin-top:10px;

    padding:10px;

    border-radius:13px;

    background:#f5faf8;

    border:1px solid #e3eee9;

    align-items:center;

    gap:10px;
}


.logo-preview{

    width:48px;

    height:48px;

    border-radius:50%;

    object-fit:contain;

    background:#ffffff;

    border:1px solid #dfe9e5;

    padding:4px;
}


.logo-preview-text{

    font-size:12px;

    color:#68756f;

    line-height:1.4;

    word-break:break-word;
}


/* ============================================================
   AMOUNT
============================================================ */

.amount-wrap{

    position:relative;
}


.amount-symbol{

    position:absolute;

    left:15px;

    top:50%;

    transform:translateY(-50%);

    font-size:16px;

    font-weight:700;

    color:#2e9f7a;

    pointer-events:none;
}


.amount-input{

    padding-left:34px;
}


/* ============================================================
   UPI
============================================================ */

.upi-input{

    padding-right:80px;
}


.paste-btn{

    position:absolute;

    right:6px;

    top:6px;

    height:40px;

    padding:0 13px;

    border:0;

    border-radius:10px;

    background:#e6f4ef;

    color:#237f62;

    font-size:12px;

    font-weight:800;

    cursor:pointer;
}


.paste-btn:hover{

    background:#d9eee7;
}


/* ============================================================
   EXPIRY
============================================================ */

.expiry-row{

    display:flex;

    gap:8px;

    width:100%;
}


.expiry-number{

    flex:1;

    min-width:0;
}


.expiry-unit{

    width:145px;

    height:52px;

    border:1px solid #dce6e2;

    border-radius:13px;

    background:#fafcfc;

    padding:0 13px;

    outline:none;

    color:#17201d;

    font-size:15px;

    cursor:pointer;
}


.expiry-unit:focus{

    border-color:#2e9f7a;

    box-shadow:
        0 0 0 3px rgba(46,159,122,.10);
}


.expiry-info{

    margin-top:7px;

    font-size:11px;

    color:#87928e;

    line-height:1.5;
}


.expiry-preview{

    margin-top:8px;

    padding:9px 11px;

    border-radius:10px;

    background:#eaf7f2;

    color:#237f62;

    font-size:12px;

    font-weight:700;

    display:none;
}


/* ============================================================
   GENERATE BUTTON
============================================================ */

.generate-btn{

    width:100%;

    height:54px;

    border:0;

    border-radius:14px;

    background:#2e9f7a;

    color:#ffffff;

    font-size:16px;

    font-weight:800;

    cursor:pointer;

    margin-top:5px;

    transition:.2s;
}


.generate-btn:hover{

    background:#258b6a;

    transform:translateY(-1px);
}


/* ============================================================
   RESULT
============================================================ */

.result-box{

    display:none;

    margin-top:25px;

    padding-top:25px;

    border-top:1px solid #edf1ef;
}


/* ============================================================
   SUCCESS
============================================================ */

.success-icon{

    width:50px;

    height:50px;

    border-radius:50%;

    background:#e6f6f0;

    color:#2e9f7a;

    display:flex;

    align-items:center;

    justify-content:center;

    margin:0 auto 10px;

    font-size:23px;

    font-weight:800;
}


.success-title{

    text-align:center;

    font-size:18px;

    font-weight:800;

    margin-bottom:5px;
}


.success-text{

    text-align:center;

    color:#78847f;

    font-size:12px;

    margin-bottom:16px;
}


/* ============================================================
   LINK
============================================================ */

.link-label{

    font-size:13px;

    font-weight:800;

    color:#34423e;

    margin-bottom:7px;
}


.link-row{

    display:flex;

    gap:7px;

    width:100%;
}


.link-input{

    flex:1;

    min-width:0;

    height:48px;

    border:1px solid #dce6e2;

    border-radius:12px;

    background:#f8faf9;

    padding:0 12px;

    color:#53615c;

    font-size:12px;

    outline:none;
}


.copy-link-btn{

    height:48px;

    padding:0 14px;

    border:0;

    border-radius:12px;

    background:#17201d;

    color:#ffffff;

    font-size:12px;

    font-weight:800;

    cursor:pointer;

    white-space:nowrap;
}


.copy-link-btn:hover{

    background:#000000;
}


/* ============================================================
   OPEN PAYMENT
============================================================ */

.open-btn{

    width:100%;

    height:49px;

    margin-top:10px;

    border:1px solid #dce6e2;

    border-radius:12px;

    background:#ffffff;

    color:#2e9f7a;

    font-size:13px;

    font-weight:800;

    cursor:pointer;
}


.open-btn:hover{

    background:#f2f8f5;
}


/* ============================================================
   DETAILS
============================================================ */

.details{

    background:#f5faf8;

    border-radius:14px;

    padding:13px;

    margin-top:15px;
}


.detail-row{

    display:flex;

    justify-content:space-between;

    gap:15px;

    padding:5px 0;

    font-size:12px;
}


.detail-label{

    color:#7b8782;
}


.detail-value{

    text-align:right;

    font-weight:700;

    word-break:break-word;
}


.detail-amount{

    color:#2e9f7a;

    font-size:15px;
}


/* ============================================================
   COPY MESSAGE
============================================================ */

.copy-message{

    display:none;

    text-align:center;

    color:#2e9f7a;

    font-size:12px;

    font-weight:700;

    margin-top:9px;
}


/* ============================================================
   NEW LINK
============================================================ */

.new-link-btn{

    width:100%;

    height:45px;

    margin-top:10px;

    border:0;

    border-radius:11px;

    background:#eef3f1;

    color:#3f4d48;

    font-size:12px;

    font-weight:800;

    cursor:pointer;
}


.new-link-btn:hover{

    background:#e5ece9;
}


/* ============================================================
   FOOTER
============================================================ */

.footer{

    text-align:center;

    color:#9aa5a1;

    font-size:11px;

    margin-top:18px;
}


/* ============================================================
   MOBILE
============================================================ */

@media(max-width:480px){

    body{

        padding:13px 9px;
    }


    .card{

        padding:22px 17px;

        border-radius:21px;
    }


    .company-logo{

        width:64px;

        height:64px;
    }


    .company-name{

        font-size:20px;
    }


    .page-title{

        font-size:22px;
    }


    .link-row{

        gap:5px;
    }


    .copy-link-btn{

        padding:0 11px;
    }


    .expiry-row{

        gap:6px;
    }


    .expiry-unit{

        width:130px;
    }

}

</style>

</head>


<body>


<div class="container">


<div class="card">


    <!-- ======================================================
         COMPANY
    ======================================================= -->

    <div class="company-header">

        <img
            src="<?php
            echo htmlspecialchars(
                $defaultLogo,
                ENT_QUOTES,
                'UTF-8'
            );
            ?>"
            class="company-logo"
            id="companyLogo"
            alt="Company Logo"
            onerror="logoError()"
        >

        <div
            class="company-name"
            id="companyName"
        >

            <?php
            echo htmlspecialchars(
                $defaultCompanyName,
                ENT_QUOTES,
                'UTF-8'
            );
            ?>

        </div>

    </div>


    <!-- ======================================================
         TITLE
    ======================================================= -->

    <div class="page-title">

        Create Payment Link

    </div>


    <div class="page-subtitle">

        Create a UPI payment link with your
        company details, amount and custom expiry.

    </div>


    <!-- ======================================================
         COMPANY NAME
    ======================================================= -->

    <div class="form-group">

        <label class="form-label">

            Company Name

        </label>


        <input
            type="text"
            id="inputCompanyName"
            class="form-input"
            value="<?php
            echo htmlspecialchars(
                $defaultCompanyName,
                ENT_QUOTES,
                'UTF-8'
            );
            ?>"
            placeholder="Enter company name"
            autocomplete="off"
            oninput="updateCompanyName()"
        >

    </div>


    <!-- ======================================================
         LOGO URL / PATH
    ======================================================= -->

    <div class="form-group">

        <label class="form-label">

            Company Logo URL / Path

            <span
                style="
                    font-weight:400;
                    color:#9aa5a1;
                "
            >
                (Optional)
            </span>

        </label>


        <input
            type="text"
            id="inputLogo"
            class="form-input"
            value="<?php
            echo htmlspecialchars(
                $defaultLogo,
                ENT_QUOTES,
                'UTF-8'
            );
            ?>"
            placeholder="logo.png or https://example.com/logo.png"
            autocomplete="off"
            oninput="handleLogoURL()"
        >


        <div class="logo-url-help">

            Enter a public logo URL or path.

            Example:

            <strong>
                logo.png
            </strong>

            or

            <strong>
                https://yourdomain.com/logo.png
            </strong>

            <br>

            The same URL will be used on the payment page.

        </div>


        <div
            class="logo-preview-box"
            id="logoPreviewBox"
        >

            <img
                src=""
                id="logoPreview"
                class="logo-preview"
                alt="Logo Preview"
            >

            <div class="logo-preview-text">

                Logo preview

            </div>

        </div>

    </div>


    <!-- ======================================================
         UPI ID
    ======================================================= -->

    <div class="form-group">

        <label class="form-label">

            UPI ID

        </label>


        <div class="input-wrap">

            <input
                type="text"
                id="upiId"
                class="form-input upi-input"
                placeholder="example@upi"
                autocomplete="off"
            >


            <button
                type="button"
                class="paste-btn"
                onclick="pasteUPI()"
            >

                PASTE

            </button>

        </div>

    </div>


    <!-- ======================================================
         AMOUNT
    ======================================================= -->

    <div class="form-group">

        <label class="form-label">

            Payment Amount

        </label>


        <div class="amount-wrap">

            <span class="amount-symbol">

                ₹

            </span>


            <input
                type="number"
                id="amount"
                class="form-input amount-input"
                placeholder="Enter amount"
                min="1"
                step="0.01"
                autocomplete="off"
            >

        </div>

    </div>


    <!-- ======================================================
         EXPIRY
    ======================================================= -->

    <div class="form-group">

        <label class="form-label">

            Link Valid For

        </label>


        <div class="expiry-row">

            <input
                type="number"
                id="expiryNumber"
                class="form-input expiry-number"
                value="1"
                min="1"
                step="1"
                placeholder="Enter time"
                oninput="updateExpiryPreview()"
            >


            <select
                id="expiryUnit"
                class="expiry-unit"
                onchange="updateExpiryPreview()"
            >

                <option value="60">

                    Minutes

                </option>


                <option value="3600">

                    Hours

                </option>


                <option value="86400">

                    Days

                </option>

            </select>

        </div>


        <div class="expiry-info">

            Enter any validity you want.

            Example: 5 Minutes,
            10 Minutes,
            37 Minutes,
            2 Hours,
            7 Days.

        </div>


        <div
            class="expiry-preview"
            id="expiryPreview"
        ></div>

    </div>


    <!-- ======================================================
         GENERATE
    ======================================================= -->

    <button
        type="button"
        class="generate-btn"
        onclick="generatePaymentLink()"
    >

        CREATE PAYMENT LINK

    </button>


    <!-- ======================================================
         RESULT
    ======================================================= -->

    <div
        class="result-box"
        id="resultBox"
    >

        <div class="success-icon">

            ✓

        </div>


        <div class="success-title">

            Payment Link Created

        </div>


        <div class="success-text">

            Copy the link or open the payment page.

        </div>


        <div class="link-label">

            Payment Link

        </div>


        <div class="link-row">

            <input
                type="text"
                id="generatedLink"
                class="link-input"
                readonly
            >


            <button
                type="button"
                class="copy-link-btn"
                onclick="copyPaymentLink()"
            >

                COPY

            </button>

        </div>


        <button
            type="button"
            class="open-btn"
            onclick="openPaymentPage()"
        >

            ↗ OPEN PAYMENT PAGE

        </button>


        <div class="details">


            <div class="detail-row">

                <span class="detail-label">

                    Company

                </span>


                <span
                    class="detail-value"
                    id="showCompany"
                ></span>

            </div>


            <div class="detail-row">

                <span class="detail-label">

                    Logo

                </span>


                <span
                    class="detail-value"
                    id="showLogo"
                ></span>

            </div>


            <div class="detail-row">

                <span class="detail-label">

                    UPI ID

                </span>


                <span
                    class="detail-value"
                    id="showUPI"
                ></span>

            </div>


            <div class="detail-row">

                <span class="detail-label">

                    Amount

                </span>


                <span
                    class="detail-value detail-amount"
                    id="showAmount"
                ></span>

            </div>


            <div class="detail-row">

                <span class="detail-label">

                    Valid For

                </span>


                <span
                    class="detail-value"
                    id="showValidFor"
                ></span>

            </div>


            <div class="detail-row">

                <span class="detail-label">

                    Expires

                </span>


                <span
                    class="detail-value"
                    id="showExpiry"
                ></span>

            </div>

        </div>


        <div
            class="copy-message"
            id="copyMessage"
        >

            Payment link copied successfully

        </div>


        <button
            type="button"
            class="new-link-btn"
            onclick="createNewLink()"
        >

            CREATE NEW LINK

        </button>

    </div>

</div>


<div class="footer">

    Powered by

    <?php
    echo htmlspecialchars(
        $defaultCompanyName,
        ENT_QUOTES,
        'UTF-8'
    );
    ?>

</div>


</div>


<script>

/* ============================================================
   GLOBAL
============================================================ */

let generatedPaymentLink = "";


/* ============================================================
   DEFAULT VALUES
============================================================ */

const defaultLogo =
<?php
echo json_encode(
    $defaultLogo,
    JSON_UNESCAPED_SLASHES |
    JSON_UNESCAPED_UNICODE
);
?>;


const defaultCompany =
<?php
echo json_encode(
    $defaultCompanyName,
    JSON_UNESCAPED_SLASHES |
    JSON_UNESCAPED_UNICODE
);
?>;


/* ============================================================
   UPDATE COMPANY NAME
============================================================ */

function updateCompanyName(){

    const company =
        document
        .getElementById("inputCompanyName")
        .value
        .trim();


    document
        .getElementById("companyName")
        .textContent =
            company ||
            defaultCompany;

}


/* ============================================================
   LOGO URL PREVIEW
============================================================ */

function handleLogoURL(){

    const logo =
        document
        .getElementById("inputLogo")
        .value
        .trim();


    const companyLogo =
        document
        .getElementById("companyLogo");


    const previewBox =
        document
        .getElementById("logoPreviewBox");


    const preview =
        document
        .getElementById("logoPreview");


    if(!logo){

        companyLogo.src =
            defaultLogo;

        previewBox.style.display =
            "none";

        return;

    }


    companyLogo.src =
        logo;


    preview.src =
        logo;


    previewBox.style.display =
        "flex";


    preview.onerror =
        function(){

            previewBox.style.display =
                "none";

        };

}


/* ============================================================
   LOGO ERROR
============================================================ */

function logoError(){

    const logo =
        document
        .getElementById("inputLogo")
        .value
        .trim();


    if(logo){

        /*
         * Do not break the payment page.
         * Simply hide broken preview image.
         */

        document
            .getElementById("companyLogo")
            .style.display =
                "none";

    }

}


/* ============================================================
   PASTE UPI
============================================================ */

async function pasteUPI(){

    try{

        const text =
            await navigator
            .clipboard
            .readText();


        if(text){

            document
                .getElementById("upiId")
                .value =
                    text.trim();

        }

    }catch(error){

        alert(
            "Clipboard permission denied. " +
            "Please paste the UPI ID manually."
        );

    }

}


/* ============================================================
   EXPIRY PREVIEW
============================================================ */

function updateExpiryPreview(){

    const number =
        parseInt(
            document
            .getElementById("expiryNumber")
            .value
        );


    const expirySelect =
        document
        .getElementById("expiryUnit");


    const unitSeconds =
        parseInt(
            expirySelect.value
        );


    const unitText =
        expirySelect
        .options[
            expirySelect.selectedIndex
        ]
        .text;


    const preview =
        document
        .getElementById("expiryPreview");


    if(
        !number ||
        number <= 0
    ){

        preview.style.display =
            "none";

        return;

    }


    preview.textContent =
        "✓ Payment link will be valid for " +
        number +
        " " +
        unitText;


    preview.style.display =
        "block";

}


/* ============================================================
   GENERATE PAYMENT LINK
============================================================ */

function generatePaymentLink(){

    const company =
        document
        .getElementById("inputCompanyName")
        .value
        .trim();


    const logoURL =
        document
        .getElementById("inputLogo")
        .value
        .trim();


    const upi =
        document
        .getElementById("upiId")
        .value
        .trim();


    const amount =
        document
        .getElementById("amount")
        .value
        .trim();


    const expiryNumber =
        parseInt(
            document
            .getElementById("expiryNumber")
            .value
        );


    const expiryUnit =
        parseInt(
            document
            .getElementById("expiryUnit")
            .value
        );


    /* ========================================================
       VALIDATION
    ======================================================== */

    if(!company){

        alert(
            "Please enter company name."
        );

        return;

    }


    if(!logoURL){

        alert(
            "Please enter Logo URL / Path."
        );

        return;

    }


    if(!upi){

        alert(
            "Please enter UPI ID."
        );

        return;

    }


    if(!upi.includes("@")){

        alert(
            "Please enter a valid UPI ID."
        );

        return;

    }


    if(!amount){

        alert(
            "Please enter payment amount."
        );

        return;

    }


    if(
        isNaN(parseFloat(amount)) ||
        parseFloat(amount) <= 0
    ){

        alert(
            "Please enter a valid amount."
        );

        return;

    }


    if(
        !expiryNumber ||
        expiryNumber <= 0
    ){

        alert(
            "Please enter link validity."
        );

        return;

    }


    if(
        !expiryUnit ||
        expiryUnit <= 0
    ){

        alert(
            "Please select validity unit."
        );

        return;

    }


    /* ========================================================
       EXPIRY
    ======================================================== */

    const totalExpirySeconds =
        expiryNumber *
        expiryUnit;


    const expiresAt =
        Math.floor(
            Date.now() / 1000
        ) +
        totalExpirySeconds;


    /* ========================================================
       PAYMENT DATA
    ======================================================== */

    const paymentData = {

        company:
            company,

        logo:
            logoURL,

        upi:
            upi,

        amount:
            parseFloat(amount)
            .toFixed(2),

        expires:
            expiresAt,

        valid_for:
            totalExpirySeconds

    };


    /* ========================================================
       JSON
    ======================================================== */

    const json =
        JSON.stringify(
            paymentData
        );


    /* ========================================================
       UTF-8 SAFE BASE64
    ======================================================== */

    let encoded;


    try{

        encoded =
            btoa(
                unescape(
                    encodeURIComponent(
                        json
                    )
                )
            );

    }catch(error){

        alert(
            "Unable to create payment link."
        );

        return;

    }


    /* ========================================================
       CURRENT DIRECTORY
    ======================================================== */

    const currentPath =
        window.location.pathname;


    const lastSlash =
        currentPath.lastIndexOf("/");


    const directory =
        currentPath.substring(
            0,
            lastSlash + 1
        );


    const baseURL =
        window.location.origin +
        directory;


    /* ========================================================
       FINAL PAYMENT LINK
    ======================================================== */

    generatedPaymentLink =
        baseURL +
        "pay.php?data=" +
        encodeURIComponent(
            encoded
        );


    /* ========================================================
       SHOW LINK
    ======================================================== */

    document
        .getElementById("generatedLink")
        .value =
            generatedPaymentLink;


    /* ========================================================
       SHOW DETAILS
    ======================================================== */

    document
        .getElementById("showCompany")
        .textContent =
            company;


    document
        .getElementById("showLogo")
        .textContent =
            logoURL;


    document
        .getElementById("showUPI")
        .textContent =
            upi;


    document
        .getElementById("showAmount")
        .textContent =
            "₹" +
            parseFloat(amount)
            .toFixed(2);


    document
        .getElementById("showValidFor")
        .textContent =
            formatDuration(
                totalExpirySeconds
            );


    document
        .getElementById("showExpiry")
        .textContent =
            new Date(
                expiresAt * 1000
            )
            .toLocaleString();


    /* ========================================================
       SHOW RESULT
    ======================================================== */

    document
        .getElementById("resultBox")
        .style.display =
            "block";


    document
        .getElementById("resultBox")
        .scrollIntoView({
            behavior:"smooth",
            block:"nearest"
        });

}


/* ============================================================
   COPY PAYMENT LINK
============================================================ */

async function copyPaymentLink(){

    const input =
        document
        .getElementById(
            "generatedLink"
        );


    const link =
        input.value;


    if(!link){

        return;

    }


    try{

        if(
            navigator.clipboard &&
            window.isSecureContext
        ){

            await navigator
                .clipboard
                .writeText(link);

        }else{

            input.select();

            document.execCommand(
                "copy"
            );

        }


        showCopyMessage();

    }catch(error){

        input.select();

        document.execCommand(
            "copy"
        );

        showCopyMessage();

    }

}


/* ============================================================
   COPY MESSAGE
============================================================ */

function showCopyMessage(){

    const message =
        document
        .getElementById(
            "copyMessage"
        );


    message.style.display =
        "block";


    setTimeout(function(){

        message.style.display =
            "none";

    },2000);

}


/* ============================================================
   OPEN PAYMENT PAGE
============================================================ */

function openPaymentPage(){

    if(!generatedPaymentLink){

        return;

    }


    window.open(
        generatedPaymentLink,
        "_blank"
    );

}


/* ============================================================
   CREATE NEW LINK
============================================================ */

function createNewLink(){

    document
        .getElementById("resultBox")
        .style.display =
            "none";


    document
        .getElementById("generatedLink")
        .value =
            "";


    generatedPaymentLink =
        "";


    window.scrollTo({

        top:0,

        behavior:"smooth"

    });

}


/* ============================================================
   FORMAT DURATION
============================================================ */

function formatDuration(seconds){

    if(seconds < 60){

        return seconds +
            " Seconds";

    }


    if(seconds < 3600){

        const minutes =
            seconds / 60;


        return minutes +
            " Minute" +
            (
                minutes !== 1
                ? "s"
                : ""
            );

    }


    if(seconds < 86400){

        const hours =
            seconds / 3600;


        return hours +
            " Hour" +
            (
                hours !== 1
                ? "s"
                : ""
            );

    }


    const days =
        seconds / 86400;


    return days +
        " Day" +
        (
            days !== 1
            ? "s"
            : ""
        );

}


/* ============================================================
   INITIAL
============================================================ */

updateExpiryPreview();

</script>


</body>

</html>