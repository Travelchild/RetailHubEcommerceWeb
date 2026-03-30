<div class="mb-8 mt-8 ml-12 mr-12">
    <h1 class="inline-flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900">
        <i class="fa-solid fa-credit-card text-brand-600" aria-hidden="true"></i>Checkout
    </h1>
    <p class="mt-1 text-sm text-slate-600">Choose cash on delivery or Visa / Mastercard, confirm shipping, then download
        your PDF invoice.</p>

    <?php if (!empty($error)): ?>
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <i class="fa-solid fa-circle-exclamation mr-2"></i><?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            <p><i class="fa-solid fa-circle-check mr-2"></i><?= htmlspecialchars($success) ?></p>
            <?php if (!empty($lastOrderId)): ?>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="index.php?page=invoice&id=<?= (int) $lastOrderId ?>"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">
                        <i class="fa-solid fa-file-pdf"></i>Download invoice (PDF)
                    </a>
                    <a href="index.php?page=orders"
                        class="inline-flex items-center gap-2 rounded-xl border border-emerald-300 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-900 hover:bg-emerald-50">
                        <i class="fa-solid fa-clock-rotate-left"></i>Order history
                    </a>
                    <a href="index.php?page=products"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 hover:bg-slate-50">
                        <i class="fa-solid fa-bag-shopping"></i>Continue shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($lastOrderId)): ?>

        <style>
            /* ── Card input enhancements ── */
            .card-field-wrap {
                position: relative;
            }

            .card-field-wrap input {
                padding-right: 48px;
            }

            .card-network-icon {
                position: absolute;
                right: 12px;
                top: 50%;
                transform: translateY(-50%);
                font-size: 22px;
                pointer-events: none;
                transition: opacity .2s;
            }

            .card-valid-tick {
                position: absolute;
                right: 12px;
                top: 50%;
                transform: translateY(-50%);
                color: #10b981;
                font-size: 14px;
                pointer-events: none;
                display: none;
            }

            /* animated card preview */
            .card-preview-wrap {
                perspective: 1000px;
                margin-bottom: 24px;
                display: flex;
                justify-content: center;
            }

            .card-preview {
                width: 340px;
                max-width: 100%;
                height: 200px;
                border-radius: 20px;
                background: linear-gradient(135deg, #131921 0%, #232f3e 50%, #37475a 100%);
                position: relative;
                box-shadow: 0 24px 60px rgba(0, 0, 0, .25);
                color: white;
                font-family: 'Outfit', monospace;
                padding: 26px 28px;
                overflow: hidden;
                transition: transform .4s cubic-bezier(.34, 1.2, .64, 1);
            }

            .card-preview:hover {
                transform: rotateY(-6deg) rotateX(4deg);
            }

            .card-preview::before {
                content: '';
                position: absolute;
                top: -60px;
                right: -60px;
                width: 200px;
                height: 200px;
                border-radius: 50%;
                background: rgba(255, 153, 0, .12);
                filter: blur(40px);
            }

            .card-preview::after {
                content: '';
                position: absolute;
                bottom: -40px;
                left: -40px;
                width: 160px;
                height: 160px;
                border-radius: 50%;
                background: rgba(99, 102, 241, .12);
                filter: blur(40px);
            }

            .cp-chip {
                width: 40px;
                height: 30px;
                background: linear-gradient(135deg, #fbbf24, #f59e0b);
                border-radius: 6px;
                margin-bottom: 22px;
                position: relative;
                z-index: 1;
                box-shadow: 0 2px 8px rgba(0, 0, 0, .2);
            }

            .cp-chip::before {
                content: '';
                position: absolute;
                left: 50%;
                top: 0;
                bottom: 0;
                width: 1px;
                background: rgba(0, 0, 0, .2);
                transform: translateX(-50%);
            }

            .cp-chip::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 0;
                right: 0;
                height: 1px;
                background: rgba(0, 0, 0, .2);
                transform: translateY(-50%);
            }

            .cp-number {
                font-size: 18px;
                letter-spacing: .18em;
                font-weight: 700;
                color: white;
                text-shadow: 0 2px 4px rgba(0, 0, 0, .3);
                margin-bottom: 18px;
                position: relative;
                z-index: 1;
                font-family: 'Courier New', monospace;
                word-spacing: .3em;
            }

            .cp-number .cp-digit {
                transition: color .2s;
            }

            .cp-bottom {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                position: relative;
                z-index: 1;
            }

            .cp-label {
                font-size: 9px;
                letter-spacing: .1em;
                text-transform: uppercase;
                color: rgba(255, 255, 255, .5);
                margin-bottom: 3px;
            }

            .cp-value {
                font-size: 13px;
                font-weight: 600;
                color: white;
                letter-spacing: .04em;
                min-height: 18px;
            }

            .cp-network {
                font-size: 32px;
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, .3));
            }

            /* field focus glow */
            .co-input {
                width: 100%;
                border: 2px solid #e2e8f0;
                border-radius: 12px;
                padding: 11px 14px;
                font-size: 14px;
                color: #0f172a;
                background: #fafafa;
                outline: none;
                transition: border-color .2s, box-shadow .2s, background .2s;
                font-family: 'DM Sans', sans-serif;
            }

            .co-input:focus {
                border-color: #ff9900;
                background: #fff;
                box-shadow: 0 0 0 3px rgba(255, 153, 0, .12);
            }

            .co-input.valid {
                border-color: #10b981;
                background: #f0fdf4;
            }

            .co-input.error {
                border-color: #ef4444;
                background: #fef2f2;
            }

            /* validation hint */
            .co-hint {
                font-size: 11.5px;
                margin-top: 4px;
                min-height: 16px;
                display: flex;
                align-items: center;
                gap: 4px;
            }

            .co-hint.ok {
                color: #10b981;
            }

            .co-hint.err {
                color: #ef4444;
            }

            .co-hint.tip {
                color: #94a3b8;
            }

            /* card panel */
            #card-payment-details {
                border-radius: 20px;
                border: 1.5px solid #e2e8f0;
                background: #f8fafc;
                padding: 24px;
                margin-top: 4px;
            }

            #card-payment-details h3 {
                font-size: 15px;
                font-weight: 700;
                color: #0f172a;
                margin-bottom: 4px;
            }

            #card-payment-details .card-note {
                font-size: 12px;
                color: #94a3b8;
                margin-bottom: 20px;
            }

            .co-label {
                display: block;
                font-size: 12.5px;
                font-weight: 700;
                color: #374151;
                margin-bottom: 6px;
            }

            .co-field-group {
                margin-bottom: 16px;
            }
        </style>

        <div class="mt-8 grid gap-8 lg:grid-cols-3">

            <!-- ── LEFT: form ── -->
            <div class="lg:col-span-2">
                <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-soft sm:p-8">

                    <!-- Order summary -->
                    <div
                        class="flex flex-col gap-3 border-b border-slate-100 pb-6 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="inline-flex items-center gap-2 text-lg font-semibold text-slate-900">
                            <i class="fa-solid fa-receipt text-brand-600 text-base"></i>Order summary
                        </h2>
                        <span
                            class="inline-flex w-fit items-center gap-2 rounded-full bg-brand-50 px-4 py-1.5 text-sm font-bold text-brand-700">
                            <i class="fa-solid fa-tag text-xs opacity-80"></i>Total <?= formatCurrency($total) ?>
                        </span>
                    </div>

                    <?php if (!empty($lineItems)): ?>
                        <ul class="mt-6 divide-y divide-slate-100 text-sm">
                            <?php foreach ($lineItems as $row):
                                $p = $row['product']; ?>
                                <li class="flex flex-wrap items-center justify-between gap-3 py-4">
                                    <span class="font-medium text-slate-900"><?= htmlspecialchars($p['name']) ?></span>
                                    <span class="text-slate-600">× <?= (int) $row['qty'] ?></span>
                                    <span class="font-semibold text-slate-900"><?= formatCurrency($row['subtotal']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <form method="post" class="mt-8 space-y-8" id="checkoutForm" novalidate>

                        <!-- Shipping address -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Shipping address</label>
                            <textarea name="shipping_address" rows="4" required
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-900 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"><?= htmlspecialchars(currentUser()['address'] ?? '') ?></textarea>
                        </div>

                        <!-- Payment method -->
                        <div>
                            <p class="text-sm font-medium text-slate-700">Payment method</p>
                            <p class="mt-1 text-xs text-slate-500">Card option is a demo — payment is simulated; no real
                                charge is made.</p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <?php foreach (($gateways ?? []) as $key => $gw): ?>
                                    <label
                                        class="relative flex cursor-pointer rounded-2xl border border-slate-200 bg-slate-50/50 p-4 transition hover:border-brand-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/80 has-[:checked]:shadow-sm">
                                        <input type="radio" name="payment_gateway" value="<?= htmlspecialchars($key) ?>"
                                            class="peer mt-1 h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-500"
                                            <?= $key === 'cod' ? 'checked' : '' ?> required>
                                        <span class="ml-3 block">
                                            <span class="flex items-center gap-2 font-semibold text-slate-900">
                                                <i
                                                    class="<?= htmlspecialchars($gw['icon']) ?> text-brand-600"></i><?= htmlspecialchars($gw['label']) ?>
                                            </span>
                                            <span
                                                class="mt-1 block text-xs text-slate-600"><?= htmlspecialchars($gw['blurb']) ?></span>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- ════════════════════════════════════════
                     CARD DETAILS PANEL
                ════════════════════════════════════════ -->
                        <div id="card-payment-details" class="hidden" aria-hidden="true">
                            <h3><i class="fa-solid fa-credit-card mr-2" style="color:#ff9900;"></i>Card Details</h3>
                            <p class="card-note">Demo only — no real payment is processed. Fill in any test details.</p>

                            <!-- Live card preview -->
                            <div class="card-preview-wrap">
                                <div class="card-preview" id="cardPreview">
                                    <div class="cp-chip"></div>
                                    <div class="cp-number" id="cpNumber">
                                        •••• &nbsp; •••• &nbsp; •••• &nbsp; ••••
                                    </div>
                                    <div class="cp-bottom">
                                        <div>
                                            <div class="cp-label">Card Holder</div>
                                            <div class="cp-value" id="cpName">YOUR NAME</div>
                                        </div>
                                        <div>
                                            <div class="cp-label">Expires</div>
                                            <div class="cp-value" id="cpExpiry">MM/YY</div>
                                        </div>
                                        <div class="cp-network" id="cpNetwork">💳</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card holder name -->
                            <div class="co-field-group">
                                <label class="co-label" for="cardName">Name on card</label>
                                <input type="text" id="cardName" name="card_name" data-card-field autocomplete="cc-name"
                                    class="co-input" placeholder="As it appears on your card">
                                <div class="co-hint tip" id="hintName"><i class="fa-solid fa-circle-info"></i> Enter name
                                    exactly as shown on card</div>
                            </div>

                            <!-- Card number -->
                            <div class="co-field-group">
                                <label class="co-label" for="cardNumber">Card number</label>
                                <div class="card-field-wrap">
                                    <input type="text" id="cardNumber" name="card_number" data-card-field
                                        inputmode="numeric" autocomplete="cc-number" maxlength="19" class="co-input"
                                        placeholder="4111 1111 1111 1111">
                                    <span class="card-network-icon" id="cardNetworkIcon" style="opacity:.3;">💳</span>
                                </div>
                                <div class="co-hint tip" id="hintNumber"><i class="fa-solid fa-circle-info"></i> Spaces are
                                    added automatically</div>
                            </div>

                            <!-- Expiry + CVV side by side -->
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

                                <!-- Expiry -->
                                <div class="co-field-group" style="margin-bottom:0;">
                                    <label class="co-label" for="cardExpiry">Expiry date</label>
                                    <input type="text" id="cardExpiry" name="card_expiry" data-card-field
                                        inputmode="numeric" autocomplete="cc-exp" maxlength="5" class="co-input"
                                        placeholder="MM / YY">
                                    <div class="co-hint tip" id="hintExpiry"><i class="fa-solid fa-circle-info"></i> The
                                        slash is added automatically</div>
                                </div>

                                <!-- CVV -->
                                <div class="co-field-group" style="margin-bottom:0;">
                                    <label class="co-label" for="cardCvv">
                                        CVV / CVC
                                        <span title="3 digits on the back (Visa/Mastercard) or 4 on the front (Amex)"
                                            style="cursor:help; color:#94a3b8; font-weight:400;">
                                            <i class="fa-solid fa-circle-question" style="font-size:11px;"></i>
                                        </span>
                                    </label>
                                    <div class="card-field-wrap">
                                        <input type="text" id="cardCvv" name="card_cvv" data-card-field inputmode="numeric"
                                            autocomplete="cc-csc" maxlength="4" class="co-input" placeholder="•••">
                                    </div>
                                    <div class="co-hint tip" id="hintCvv"><i class="fa-solid fa-circle-info"></i> 3 digits
                                        on back (4 for Amex)</div>
                                </div>

                            </div>
                        </div>
                        <!-- /card-payment-details -->

                        <button type="submit" id="submitBtn"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-700 px-8 py-3.5 text-sm font-semibold text-white shadow-md hover:from-brand-700 hover:to-indigo-800 transition-all">
                            <i class="fa-solid fa-lock"></i>Complete payment &amp; place order
                        </button>
                    </form>
                </div>
            </div>

            <!-- ── RIGHT: trust sidebar ── -->
            <aside
                class="rounded-3xl border border-indigo-100 bg-gradient-to-b from-indigo-50/90 to-white p-6 shadow-soft self-start sticky top-4">
                <h3 class="text-sm font-semibold text-slate-900">
                    <i class="fa-solid fa-shield-halved mr-2 text-emerald-600"></i>Secure checkout
                </h3>
                <ul class="mt-4 space-y-3 text-sm text-slate-600">
                    <li class="flex gap-2"><i class="fa-solid fa-check mt-0.5 text-emerald-600"></i>PDF invoice available
                        right after confirmation</li>
                    <li class="flex gap-2"><i class="fa-solid fa-check mt-0.5 text-emerald-600"></i>Visa and Mastercard
                        accepted at checkout</li>
                    <li class="flex gap-2"><i class="fa-solid fa-check mt-0.5 text-emerald-600"></i>Cash on delivery skips
                        online capture</li>
                </ul>

                <!-- Accepted cards -->
                <div style="margin-top:24px; padding-top:20px; border-top:1px solid #e5e7eb;">
                    <p
                        style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-bottom:10px;">
                        Accepted cards</p>
                    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                        <span
                            style="background:white; border:1px solid #e5e7eb; border-radius:6px; padding:5px 10px; font-size:12px; font-weight:800; color:#1a56db;">VISA</span>
                        <span
                            style="background:white; border:1px solid #e5e7eb; border-radius:6px; padding:5px 10px; font-size:12px; font-weight:800; color:#e53e3e;">Master</span>
                        <span
                            style="background:white; border:1px solid #e5e7eb; border-radius:6px; padding:5px 10px; font-size:12px; font-weight:800; color:#2d3748;">Amex</span>
                    </div>
                </div>
            </aside>
        </div>

        <!-- ════════════════════════════════════════════════
     CARD FORMATTING + VALIDATION SCRIPT
════════════════════════════════════════════════ -->
        <script>
            (function () {
                'use strict';

                // ── DOM refs ──
                const panel = document.getElementById('card-payment-details');
                const radios = document.querySelectorAll('input[name="payment_gateway"]');
                const cardFields = document.querySelectorAll('[data-card-field]');
                const numInput = document.getElementById('cardNumber');
                const exprInput = document.getElementById('cardExpiry');
                const cvvInput = document.getElementById('cardCvv');
                const nameInput = document.getElementById('cardName');
                const netIcon = document.getElementById('cardNetworkIcon');
                const cpNumber = document.getElementById('cpNumber');
                const cpName = document.getElementById('cpName');
                const cpExpiry = document.getElementById('cpExpiry');
                const cpNetwork = document.getElementById('cpNetwork');

                // ── Show / hide card panel ──
                function syncPanel() {
                    const isCard = !!document.querySelector('input[name="payment_gateway"][value="card"]:checked');
                    panel.classList.toggle('hidden', !isCard);
                    panel.setAttribute('aria-hidden', isCard ? 'false' : 'true');
                    cardFields.forEach(el => { el.required = isCard; });
                }
                radios.forEach(r => r.addEventListener('change', syncPanel));
                syncPanel();

                // ════════════════════════════════
                // CARD NETWORK DETECTION
                // ════════════════════════════════
                function detectNetwork(raw) {
                    if (/^4/.test(raw)) return { name: 'visa', emoji: '💳', label: 'VISA', color: '#1a56db', cvvLen: 3 };
                    if (/^5[1-5]|^2[2-7]/.test(raw)) return { name: 'master', emoji: '💳', label: 'MC', color: '#e53e3e', cvvLen: 3 };
                    if (/^3[47]/.test(raw)) return { name: 'amex', emoji: '💳', label: 'AMEX', color: '#2d3748', cvvLen: 4 };
                    if (/^6/.test(raw)) return { name: 'other', emoji: '💳', label: '', color: '#6b7280', cvvLen: 3 };
                    return null;
                }

                // ════════════════════════════════
                // CARD NUMBER — auto spaces + validation
                // ════════════════════════════════
                numInput.addEventListener('input', function (e) {
                    let raw = this.value.replace(/\D/g, '');          // digits only
                    let cursor = this.selectionStart;

                    // Amex: 4-6-5, others: 4-4-4-4
                    let net = detectNetwork(raw);
                    let formatted;
                    if (net && net.name === 'amex') {
                        formatted = raw.replace(/^(\d{0,4})(\d{0,6})(\d{0,5}).*/, (_, a, b, c) =>
                            [a, b, c].filter(Boolean).join(' ')
                        );
                        this.maxLength = 17; // 4+1+6+1+5
                    } else {
                        formatted = raw.replace(/(\d{4})(?=\d)/g, '$1 ').slice(0, 19);
                        this.maxLength = 19;
                    }

                    this.value = formatted;

                    // Update network icon + card preview
                    updateNetworkUI(net, raw);
                    updateCardPreview();

                    // Validation hint
                    const digits = formatted.replace(/\s/g, '').length;
                    const full = net?.name === 'amex' ? 15 : 16;
                    const hintEl = document.getElementById('hintNumber');
                    if (digits === 0) {
                        setHint(hintEl, 'tip', 'Spaces are added automatically');
                    } else if (digits < full) {
                        setHint(hintEl, 'tip', `${full - digits} more digit${full - digits > 1 ? 's' : ''} needed`);
                    } else {
                        setHint(hintEl, 'ok', '✓ Card number complete');
                        numInput.classList.add('valid');
                    }
                    if (digits < full) numInput.classList.remove('valid');
                });

                // Block non-numeric keystrokes gracefully
                numInput.addEventListener('keydown', function (e) {
                    if (['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key)) return;
                    if (!/^\d$/.test(e.key)) e.preventDefault();
                });

                function updateNetworkUI(net, raw) {
                    if (net && raw.length > 0) {
                        netIcon.textContent = net.emoji;
                        netIcon.style.opacity = '1';
                        cpNetwork.textContent = net.emoji;
                    } else {
                        netIcon.textContent = '💳';
                        netIcon.style.opacity = '0.3';
                        cpNetwork.textContent = '💳';
                    }
                    // CVV max length based on network
                    if (net) cvvInput.maxLength = net.cvvLen;
                }

                // ════════════════════════════════
                // EXPIRY DATE — auto slash + validation
                // ════════════════════════════════
                exprInput.addEventListener('input', function (e) {
                    let raw = this.value.replace(/\D/g, '');   // keep digits only

                    // After 2 digits, auto-insert "/"
                    if (raw.length > 2) {
                        raw = raw.slice(0, 2) + '/' + raw.slice(2, 4);
                    }
                    this.value = raw;
                    updateCardPreview();

                    // Validate
                    const hintEl = document.getElementById('hintExpiry');
                    if (raw.length === 0) {
                        setHint(hintEl, 'tip', 'The slash is added automatically');
                        exprInput.classList.remove('valid', 'error');
                        return;
                    }
                    if (raw.length < 5) {
                        setHint(hintEl, 'tip', 'Enter MM/YY format');
                        exprInput.classList.remove('valid', 'error');
                        return;
                    }
                    const [mm, yy] = raw.split('/').map(Number);
                    const now = new Date();
                    const nowYY = now.getFullYear() % 100;
                    const nowMM = now.getMonth() + 1;
                    if (mm < 1 || mm > 12) {
                        setHint(hintEl, 'err', '✗ Month must be 01–12');
                        exprInput.classList.add('error'); exprInput.classList.remove('valid');
                    } else if (yy < nowYY || (yy === nowYY && mm < nowMM)) {
                        setHint(hintEl, 'err', '✗ Card has expired');
                        exprInput.classList.add('error'); exprInput.classList.remove('valid');
                    } else {
                        setHint(hintEl, 'ok', '✓ Valid expiry');
                        exprInput.classList.add('valid'); exprInput.classList.remove('error');
                    }
                });

                // Prevent non-numeric on expiry (allow slash for paste)
                exprInput.addEventListener('keydown', function (e) {
                    if (['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight'].includes(e.key)) return;
                    if (!/^\d$/.test(e.key)) e.preventDefault();
                });

                // ════════════════════════════════
                // CVV — digits only + validation
                // ════════════════════════════════
                cvvInput.addEventListener('input', function () {
                    this.value = this.value.replace(/\D/g, '').slice(0, this.maxLength);
                    const hintEl = document.getElementById('hintCvv');
                    const net = detectNetwork(numInput.value.replace(/\s/g, ''));
                    const needed = net?.cvvLen ?? 3;
                    const len = this.value.length;
                    if (len === 0) {
                        setHint(hintEl, 'tip', `${needed} digits on the back of the card`);
                        cvvInput.classList.remove('valid', 'error');
                    } else if (len < needed) {
                        setHint(hintEl, 'tip', `${needed - len} more digit${needed - len > 1 ? 's' : ''}`);
                        cvvInput.classList.remove('valid', 'error');
                    } else {
                        setHint(hintEl, 'ok', '✓ CVV complete');
                        cvvInput.classList.add('valid'); cvvInput.classList.remove('error');
                    }
                });
                cvvInput.addEventListener('keydown', function (e) {
                    if (['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight'].includes(e.key)) return;
                    if (!/^\d$/.test(e.key)) e.preventDefault();
                });

                // ════════════════════════════════
                // CARD HOLDER NAME
                // ════════════════════════════════
                nameInput.addEventListener('input', function () {
                    const hintEl = document.getElementById('hintName');
                    const val = this.value.trim();
                    updateCardPreview();
                    if (!val) {
                        setHint(hintEl, 'tip', 'Enter name exactly as shown on card');
                        nameInput.classList.remove('valid', 'error');
                    } else if (val.length < 2) {
                        setHint(hintEl, 'err', '✗ Name too short');
                        nameInput.classList.add('error'); nameInput.classList.remove('valid');
                    } else {
                        setHint(hintEl, 'ok', '✓ Name entered');
                        nameInput.classList.add('valid'); nameInput.classList.remove('error');
                    }
                });

                // ════════════════════════════════
                // LIVE CARD PREVIEW UPDATE
                // ════════════════════════════════
                function updateCardPreview() {
                    // Number
                    const raw = numInput.value.replace(/\s/g, '');
                    const groups = [];
                    for (let i = 0; i < 16; i += 4) {
                        const chunk = raw.slice(i, i + 4).padEnd(4, '•');
                        groups.push(chunk);
                    }
                    cpNumber.textContent = groups.join('  ');

                    // Name
                    cpName.textContent = nameInput.value.toUpperCase().trim() || 'YOUR NAME';

                    // Expiry
                    cpExpiry.textContent = exprInput.value || 'MM/YY';
                }

                // ════════════════════════════════
                // HELPER: set hint message
                // ════════════════════════════════
                function setHint(el, type, msg) {
                    if (!el) return;
                    el.className = 'co-hint ' + type;
                    const icon = type === 'ok' ? 'fa-circle-check'
                        : type === 'err' ? 'fa-circle-xmark'
                            : 'fa-circle-info';
                    el.innerHTML = `<i class="fa-solid ${icon}"></i> ${msg}`;
                }

                // ════════════════════════════════
                // FORM SUBMIT VALIDATION
                // ════════════════════════════════
                document.getElementById('checkoutForm').addEventListener('submit', function (e) {
                    const isCard = !!document.querySelector('input[name="payment_gateway"][value="card"]:checked');
                    if (!isCard) return; // COD — let it submit normally

                    let ok = true;

                    // Name
                    if (nameInput.value.trim().length < 2) {
                        setHint(document.getElementById('hintName'), 'err', '✗ Please enter the name on the card');
                        nameInput.classList.add('error'); ok = false;
                    }

                    // Number
                    const rawNum = numInput.value.replace(/\s/g, '');
                    const net = detectNetwork(rawNum);
                    const needed = net?.name === 'amex' ? 15 : 16;
                    if (rawNum.length < needed) {
                        setHint(document.getElementById('hintNumber'), 'err', '✗ Please enter a valid card number');
                        numInput.classList.add('error'); ok = false;
                    }

                    // Expiry
                    const exprVal = exprInput.value;
                    if (!/^\d{2}\/\d{2}$/.test(exprVal)) {
                        setHint(document.getElementById('hintExpiry'), 'err', '✗ Enter expiry as MM/YY');
                        exprInput.classList.add('error'); ok = false;
                    } else {
                        const [mm, yy] = exprVal.split('/').map(Number);
                        const now = new Date();
                        const nowYY = now.getFullYear() % 100;
                        const nowMM = now.getMonth() + 1;
                        if (mm < 1 || mm > 12 || yy < nowYY || (yy === nowYY && mm < nowMM)) {
                            setHint(document.getElementById('hintExpiry'), 'err', '✗ Card is expired or date is invalid');
                            exprInput.classList.add('error'); ok = false;
                        }
                    }

                    // CVV
                    const cvvNeeded = net?.cvvLen ?? 3;
                    if (cvvInput.value.length < cvvNeeded) {
                        setHint(document.getElementById('hintCvv'), 'err', `✗ Enter ${cvvNeeded}-digit CVV`);
                        cvvInput.classList.add('error'); ok = false;
                    }

                    if (!ok) {
                        e.preventDefault();
                        // Scroll to first error
                        const firstErr = panel.querySelector('.error');
                        if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });

            })();
        </script>
    <?php endif; ?>

</div><!-- /mb-8 mt-8 ml-12 mr-12 -->