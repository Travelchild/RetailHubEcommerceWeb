<?php
// ════════════════════════════════════════════════════════
// FOOTER — RetailHub  (matches header colour/font system)
// ════════════════════════════════════════════════════════
// Expects: $lang, $_ (translation map), isLoggedIn(), isAdmin(), isSupportStaff()
// to be defined by the including page (same as header.php).
// If you include the footer separately, re-declare those or pass them in.
?>

<!-- ══════════════════════════════════════════════════════
     BACK TO TOP RIBBON
══════════════════════════════════════════════════════ -->
<div id="backToTopRibbon"
    style="background:#37475a;text-align:center;padding:13px;cursor:pointer;border-top:1px solid rgba(255,255,255,.06);transition:background .15s;"
    onmouseover="this.style.background='#485769'" onmouseout="this.style.background='#37475a'"
    onclick="window.scrollTo({top:0,behavior:'smooth'})">
    <span
        style="color:white;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;letter-spacing:.04em;display:inline-flex;align-items:center;gap:8px;">
        <i class="fa-solid fa-chevron-up" style="font-size:11px;color:var(--gold);"></i>
        Back to top
        <i class="fa-solid fa-chevron-up" style="font-size:11px;color:var(--gold);"></i>
    </span>
</div>

<!-- ══════════════════════════════════════════════════════
     MAIN FOOTER BODY
══════════════════════════════════════════════════════ -->
<footer id="mainFooter">

    <!-- ── SECTION 1: FOUR LINK COLUMNS ── -->
    <div class="rh-footer-links">
        <div class="rh-footer-inner">

            <!-- COL 1 — Get to Know Us -->
            <div class="rh-footer-col">
                <h4 class="rh-col-heading">Get to Know Us</h4>
                <ul class="rh-col-list">
                    <li><a href="index.php?page=about">About RetailHub</a></li>
                    <li><a href="index.php?page=careers">Careers</a></li>
                    <li><a href="index.php?page=press">Press Releases</a></li>
                    <li><a href="index.php?page=sustainability">Sustainability</a></li>
                    <li><a href="index.php?page=investor">Investor Relations</a></li>
                </ul>
            </div>

            <!-- COL 2 — Connect with Us -->
            <div class="rh-footer-col">
                <h4 class="rh-col-heading">Connect with Us</h4>
                <ul class="rh-col-list">
                    <li>
                        <a href="https://facebook.com" target="_blank" rel="noopener">
                            <i class="fa-brands fa-facebook rh-social-icon" style="--c:#4267B2;"></i>Facebook
                        </a>
                    </li>
                    <li>
                        <a href="https://twitter.com" target="_blank" rel="noopener">
                            <i class="fa-brands fa-x-twitter rh-social-icon" style="--c:#e7e9ea;"></i>Twitter / X
                        </a>
                    </li>
                    <li>
                        <a href="https://instagram.com" target="_blank" rel="noopener">
                            <i class="fa-brands fa-instagram rh-social-icon" style="--c:#E1306C;"></i>Instagram
                        </a>
                    </li>
                    <li>
                        <a href="https://youtube.com" target="_blank" rel="noopener">
                            <i class="fa-brands fa-youtube rh-social-icon" style="--c:#FF0000;"></i>YouTube
                        </a>
                    </li>
                    <li>
                        <a href="https://wa.me/94000000000" target="_blank" rel="noopener">
                            <i class="fa-brands fa-whatsapp rh-social-icon" style="--c:#25D366;"></i>WhatsApp
                        </a>
                    </li>
                </ul>
            </div>

            <!-- COL 3 — Make Money with Us -->
            <div class="rh-footer-col">
                <h4 class="rh-col-heading">Make Money with Us</h4>
                <ul class="rh-col-list">
                    <li><a href="index.php?page=sell">Sell on RetailHub</a></li>
                    <li><a href="index.php?page=affiliate">Become an Affiliate</a></li>
                    <li><a href="index.php?page=advertise">Advertise Your Products</a></li>
                    <li><a href="index.php?page=vendor">Vendor Central</a></li>
                    <li><a href="index.php?page=fulfillment">Fulfilment by RetailHub</a></li>
                </ul>
            </div>

            <!-- COL 4 — Let Us Help You -->
            <div class="rh-footer-col">
                <h4 class="rh-col-heading">Let Us Help You</h4>
                <ul class="rh-col-list">
                    <li><a href="index.php?page=account">Your Account</a></li>
                    <li><a href="index.php?page=orders">Your Orders</a></li>
                    <li><a href="index.php?page=shipping">Shipping Rates &amp; Policies</a></li>
                    <li><a href="index.php?page=returns">Returns &amp; Replacements</a></li>
                    <li><a href="index.php?page=support">Customer Service</a></li>
                    <li><a href="index.php?page=faq">Help &amp; FAQ</a></li>
                </ul>
            </div>

        </div>
    </div>

    <!-- ── SECTION 2: LOGO + TRUST BADGES ── -->
    <div class="rh-footer-mid">
        <div class="rh-footer-inner rh-footer-mid-inner">

            <!-- Logo -->
            <a href="index.php" class="rh-footer-logo">
                <div class="rh-footer-logo-icon"><i class="fa-solid fa-store"></i></div>
                <div>
                    <div class="rh-footer-logo-text">Retail<span>Hub</span></div>
                    <div class="rh-footer-logo-sub">PREMIUM STORE · SRI LANKA</div>
                </div>
            </a>

            <!-- Trust badges -->
            <div class="rh-trust-row">
                <div class="rh-trust-badge">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Secure Checkout</span>
                </div>
                <div class="rh-trust-badge">
                    <i class="fa-solid fa-truck-fast"></i>
                    <span>Fast Delivery</span>
                </div>
                <div class="rh-trust-badge">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Easy Returns</span>
                </div>
                <div class="rh-trust-badge">
                    <i class="fa-solid fa-headset"></i>
                    <span>24/7 Support</span>
                </div>
            </div>



        </div>
    </div>

    <!-- ── SECTION 3: PAYMENT + LEGAL BOTTOM ── -->
    <div class="rh-footer-bottom">
        <div class="rh-footer-inner rh-footer-bottom-inner">

            <!-- Payment methods -->
            <div class="rh-pay-row">
                <span class="rh-pay-label">We accept</span>
                <div class="rh-pay-icons">
                    <div class="rh-pay-chip" title="Visa"><i class="fa-brands fa-cc-visa"></i></div>
                    <div class="rh-pay-chip" title="Mastercard"><i class="fa-brands fa-cc-mastercard"></i></div>
                    <div class="rh-pay-chip" title="PayPal"><i class="fa-brands fa-cc-paypal"></i></div>
                    <div class="rh-pay-chip" title="Apple Pay"><i class="fa-brands fa-apple-pay"></i></div>
                    <div class="rh-pay-chip" title="Google Pay"><i class="fa-brands fa-google-pay"></i></div>
                    <div class="rh-pay-chip rh-pay-cash" title="Cash on Delivery">
                        <i class="fa-solid fa-money-bill-wave"></i><span>COD</span>
                    </div>
                </div>
            </div>

            <!-- Legal links -->
            <div class="rh-legal-row">
                <span class="rh-copyright">
                    <i class="fa-regular fa-copyright" style="opacity:.6;margin-right:3px;"></i>
                    <?= date('Y') ?> RetailHub. All rights reserved.
                </span>

            </div>

        </div>
    </div>
</footer>

<!-- ══════════════════════════════════════════════════════
     FOOTER STYLES
══════════════════════════════════════════════════════ -->
<style>
    /* ─── CSS variables (match header) ─── */
    :root {
        --navy-deep: #131921;
        --navy-mid: #232f3e;
        --navy-light: #37475a;
        --gold: #ff9900;
    }

    /* ─── MAIN FOOTER BODY ─── */
    #mainFooter {
        font-family: 'DM Sans', 'Noto Sans Sinhala', 'Noto Sans Tamil', system-ui, sans-serif;
        background: var(--navy-mid);
        color: rgba(255, 255, 255, .75);
        margin-top: auto;
    }

    /* shared inner wrapper */
    .rh-footer-inner {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* ─── LINK SECTION ─── */
    .rh-footer-links {
        border-top: 1px solid rgba(255, 255, 255, .08);
        padding: 44px 0 36px;
    }

    .rh-footer-inner:has(.rh-footer-col) {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 36px;
    }

    @media (max-width:900px) {
        .rh-footer-inner:has(.rh-footer-col) {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width:480px) {
        .rh-footer-inner:has(.rh-footer-col) {
            grid-template-columns: 1fr;
        }
    }

    .rh-col-heading {
        font-family: 'Outfit', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: white;
        margin: 0 0 14px;
        padding-bottom: 8px;
        border-bottom: 2px solid rgba(255, 153, 0, .35);
        display: inline-block;
    }

    .rh-col-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .rh-col-list a {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: rgba(255, 255, 255, .65);
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 400;
        transition: color .15s, padding-left .15s;
    }

    .rh-col-list a:hover {
        color: var(--gold);
        padding-left: 3px;
    }

    .rh-social-icon {
        color: var(--c, rgba(255, 255, 255, .5));
        font-size: 14px;
        width: 18px;
        text-align: center;
        flex-shrink: 0;
        transition: transform .2s;
    }

    .rh-col-list a:hover .rh-social-icon {
        transform: scale(1.25);
    }

    /* ─── MID SECTION ─── */
    .rh-footer-mid {
        background: var(--navy-deep);
        border-top: 1px solid rgba(255, 255, 255, .07);
        padding: 24px 0;
    }

    .rh-footer-mid-inner {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px 32px;
    }

    /* Logo */
    .rh-footer-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        flex-shrink: 0;
    }

    .rh-footer-logo-icon {
        width: 38px;
        height: 38px;
        background: linear-gradient(135deg, var(--gold) 0%, #ff6b00 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: var(--navy-deep);
        flex-shrink: 0;
    }

    .rh-footer-logo-text {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 19px;
        color: white;
        line-height: 1;
    }

    .rh-footer-logo-text span {
        color: var(--gold);
    }

    .rh-footer-logo-sub {
        font-size: 9px;
        font-weight: 500;
        color: var(--gold);
        letter-spacing: .08em;
        margin-top: 3px;
    }

    /* Trust badges */
    .rh-trust-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        flex: 1;
        justify-content: end;
    }

    .rh-trust-badge {
        display: flex;
        align-items: center;
        gap: 7px;
        background: rgba(255, 255, 255, .06);
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 20px;
        padding: 7px 14px;
        font-size: 12px;
        font-weight: 600;
        color: rgba(255, 255, 255, .8);
        transition: border-color .15s, background .15s;
        white-space: nowrap;
    }

    .rh-trust-badge i {
        color: var(--gold);
        font-size: 13px;
    }

    .rh-trust-badge:hover {
        border-color: var(--gold);
        background: rgba(255, 153, 0, .08);
        color: white;
    }

    /* Language chips */
    .rh-lang-row {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    .rh-lang-chip {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, .15);
        background: none;
        color: rgba(255, 255, 255, .65);
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all .15s;
    }

    .rh-lang-chip:hover {
        border-color: var(--gold);
        color: var(--gold);
    }

    .rh-lang-chip.active {
        border-color: var(--gold);
        background: rgba(255, 153, 0, .15);
        color: var(--gold);
    }

    /* ─── BOTTOM SECTION ─── */
    .rh-footer-bottom {
        background: #0d1117;
        border-top: 1px solid rgba(255, 255, 255, .05);
        padding: 18px 0;
    }

    .rh-footer-bottom-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
    }

    /* Payment row */
    .rh-pay-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .rh-pay-label {
        font-size: 11.5px;
        font-weight: 600;
        color: rgba(255, 255, 255, .45);
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .rh-pay-icons {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .rh-pay-chip {
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .08);
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 22px;
        color: rgba(255, 255, 255, .7);
        transition: all .15s;
        min-width: 48px;
        height: 34px;
    }

    .rh-pay-chip:hover {
        background: rgba(255, 255, 255, .13);
        border-color: var(--gold);
        color: white;
    }

    .rh-pay-cash {
        font-size: 13px;
        gap: 5px;
        padding: 6px 12px;
    }

    .rh-pay-cash i {
        font-size: 14px;
        color: #4ade80;
    }

    .rh-pay-cash span {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
    }

    /* Legal row */
    .rh-legal-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px 18px;
        justify-content: center;
    }

    .rh-copyright {
        font-size: 12px;
        color: rgba(255, 255, 255, .4);
    }

    .rh-legal-links {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px 10px;
        justify-content: center;
    }

    .rh-legal-links a {
        font-size: 12px;
        color: rgba(255, 255, 255, .45);
        text-decoration: none;
        transition: color .15s;
    }

    .rh-legal-links a:hover {
        color: var(--gold);
    }

    .rh-legal-sep {
        color: rgba(255, 255, 255, .2);
        font-size: 11px;
    }

    /* Country note */
    .rh-country-note {
        font-size: 11.5px;
        color: rgba(255, 255, 255, .3);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* ─── RESPONSIVE ─── */
    @media (max-width:700px) {
        .rh-footer-mid-inner {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .rh-trust-row {
            justify-content: center;
        }

        .rh-lang-row {
            justify-content: center;
        }

        .rh-legal-row {
            flex-direction: column;
            gap: 8px;
        }
    }
</style>

<script src="../assets/js/app.js"></script>
</body>

</html>