<?php
// views/home.php
$db = Database::connection();



// Fetch top 4 best-selling products
$bestSellers = [];
try {
    $bestSellers = $db->query("
        SELECT p.id, p.name, p.brand, p.price, p.image_url, p.stock_qty,
               COALESCE(SUM(oi.quantity), 0) AS units_sold,
               c.name AS category_name
        FROM products p
        LEFT JOIN order_items oi ON oi.product_id = p.id
        LEFT JOIN orders o ON o.id = oi.order_id AND o.order_status != 'Cancelled'
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE p.is_active = 1
        GROUP BY p.id
        ORDER BY units_sold DESC, p.created_at DESC
        LIMIT 4
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $bestSellers = [];
}
?> <!-- ══════════════════════════════════════════════════════════
     RETAILHUB — EDITORIAL PARALLAX HERO SLIDER
     - Gradient backgrounds (no bg images)
     - Large 3D floating product images on right
     - Parallax scroll effect
     Drop inside <main> before feature sections.
══════════════════════════════════════════════════════════ -->

<style>
    /* ═══════════════════════════════════════════
   SLIDER WRAPPER
═══════════════════════════════════════════ */

:root {
    --cb-navy : #131921;
    --cb-navy2: #232f3e;
    --cb-gold : #ff9900;
    --cb-gold2: #e68900;
}
 
/* ── FAB Toggle Button ── */
.cb-fab {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 9998;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--cb-gold), var(--cb-gold2));
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 28px rgba(255,153,0,.45);
    transition: transform .2s, box-shadow .2s;
    animation: fabBounce 3s ease-in-out infinite;
}
.cb-fab:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 36px rgba(255,153,0,.55);
}
@keyframes fabBounce {
    0%,100% { transform: translateY(0); }
    50%      { transform: translateY(-5px); }
}
.cb-fab i {
    font-size: 24px;
    color: var(--cb-navy);
    transition: transform .3s;
}
.cb-fab.open i.cb-icon-chat { display: none; }
.cb-fab.open i.cb-icon-close { display: block !important; }
.cb-fab i.cb-icon-close { display: none; }
 
/* Pulse ring */
.cb-fab::after {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 2px solid rgba(255,153,0,.4);
    animation: cbRing 2s ease-out infinite;
}
@keyframes cbRing {
    0%   { transform: scale(1); opacity:.8; }
    100% { transform: scale(1.5); opacity:0; }
}
 
/* Notification dot */
.cb-notif {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 14px;
    height: 14px;
    background: #ef4444;
    border: 2px solid white;
    border-radius: 50%;
    animation: notifPulse 1.5s ease-in-out infinite;
}
@keyframes notifPulse {
    0%,100% { transform: scale(1); }
    50%      { transform: scale(1.2); }
}
 
/* ── Chat Window ── */
.cb-window {
    position: fixed;
    bottom: 102px;
    right: 28px;
    z-index: 9999;
    width: 370px;
    max-width: calc(100vw - 32px);
    border-radius: 22px;
    background: white;
    box-shadow: 0 24px 80px rgba(0,0,0,.22), 0 0 0 1px rgba(0,0,0,.06);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: scale(.85) translateY(20px);
    opacity: 0;
    pointer-events: none;
    transition: transform .3s cubic-bezier(.34,1.56,.64,1), opacity .25s ease;
    transform-origin: bottom right;
    max-height: 600px;
}
.cb-window.open {
    transform: scale(1) translateY(0);
    opacity: 1;
    pointer-events: auto;
}
 
/* Header */
.cb-header {
    background: linear-gradient(135deg, var(--cb-navy), var(--cb-navy2));
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}
.cb-avatar {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--cb-gold), var(--cb-gold2));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px rgba(255,153,0,.25);
}
.cb-header-info { flex: 1; }
.cb-header-name {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    font-size: 15px;
    color: white;
    display: flex;
    align-items: center;
    gap: 7px;
}
.cb-online-dot {
    width: 7px;
    height: 7px;
    background: #4ade80;
    border-radius: 50%;
    animation: onlinePulse 2s ease-in-out infinite;
}
@keyframes onlinePulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(74,222,128,.5); }
    50%      { box-shadow: 0 0 0 4px rgba(74,222,128,0); }
}
.cb-header-sub {
    font-size: 11.5px;
    color: rgba(255,255,255,.5);
    margin-top: 1px;
}
.cb-header-close {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: rgba(255,255,255,.1);
    border: none;
    color: rgba(255,255,255,.7);
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .15s;
}
.cb-header-close:hover { background: rgba(255,255,255,.2); color: white; }
 
/* Quick chips */
.cb-chips {
    padding: 10px 16px;
    display: flex;
    gap: 7px;
    flex-wrap: wrap;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
    flex-shrink: 0;
}
.cb-chip {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--cb-navy);
    background: white;
    border: 1.5px solid #e5e7eb;
    border-radius: 20px;
    padding: 5px 12px;
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
    font-family: 'DM Sans', sans-serif;
}
.cb-chip:hover {
    border-color: var(--cb-gold);
    background: #fff8e7;
    color: var(--cb-navy);
}
 
/* Messages */
.cb-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    scroll-behavior: smooth;
    min-height: 280px;
    max-height: 340px;
}
.cb-messages::-webkit-scrollbar { width: 4px; }
.cb-messages::-webkit-scrollbar-track { background: transparent; }
.cb-messages::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
 
.cb-msg {
    display: flex;
    gap: 8px;
    animation: msgIn .3s ease both;
}
@keyframes msgIn {
    from { opacity:0; transform: translateY(8px); }
    to   { opacity:1; transform: translateY(0); }
}
.cb-msg.user { flex-direction: row-reverse; }
 
.cb-msg-avatar {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    margin-top: 2px;
}
.cb-msg.bot .cb-msg-avatar  { background: linear-gradient(135deg, var(--cb-gold), var(--cb-gold2)); color: var(--cb-navy); }
.cb-msg.user .cb-msg-avatar { background: var(--cb-navy2); color: white; font-size: 11px; font-family: 'Outfit', sans-serif; font-weight: 700; }
 
.cb-bubble {
    max-width: 78%;
    padding: 10px 14px;
    border-radius: 16px;
    font-size: 13.5px;
    line-height: 1.55;
    font-family: 'DM Sans', sans-serif;
}
.cb-msg.bot .cb-bubble {
    background: #f8fafc;
    color: #1e293b;
    border: 1px solid #f1f5f9;
    border-top-left-radius: 4px;
}
.cb-msg.user .cb-bubble {
    background: linear-gradient(135deg, var(--cb-navy), var(--cb-navy2));
    color: white;
    border-bottom-right-radius: 4px;
}
 
/* Typing indicator */
.cb-typing {
    display: flex;
    gap: 8px;
    align-items: flex-end;
}
.cb-typing-dots {
    display: flex;
    gap: 4px;
    padding: 12px 16px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 16px;
    border-top-left-radius: 4px;
}
.cb-typing-dots span {
    width: 7px;
    height: 7px;
    background: #94a3b8;
    border-radius: 50%;
    animation: typingDot 1.2s ease-in-out infinite;
}
.cb-typing-dots span:nth-child(2) { animation-delay: .2s; }
.cb-typing-dots span:nth-child(3) { animation-delay: .4s; }
@keyframes typingDot {
    0%,60%,100% { transform: translateY(0); background: #94a3b8; }
    30%          { transform: translateY(-6px); background: var(--cb-gold); }
}
 
/* Input */
.cb-input-row {
    padding: 12px 16px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    gap: 8px;
    align-items: center;
    background: white;
    flex-shrink: 0;
}
.cb-input {
    flex: 1;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 13.5px;
    font-family: 'DM Sans', sans-serif;
    color: #1e293b;
    outline: none;
    resize: none;
    transition: border-color .2s;
    line-height: 1.4;
    max-height: 100px;
    overflow-y: auto;
}
.cb-input:focus { border-color: var(--cb-gold); }
.cb-input::placeholder { color: #94a3b8; }
.cb-send {
    width: 42px;
    height: 42px;
    border-radius: 11px;
    background: linear-gradient(135deg, var(--cb-gold), var(--cb-gold2));
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    color: var(--cb-navy);
    transition: transform .15s, box-shadow .15s;
    flex-shrink: 0;
    box-shadow: 0 3px 10px rgba(255,153,0,.3);
}
.cb-send:hover { transform: scale(1.08); box-shadow: 0 5px 16px rgba(255,153,0,.4); }
.cb-send:disabled { opacity: .5; cursor: not-allowed; transform: none; }
 
/* Powered by */
.cb-powered {
    text-align: center;
    font-size: 10.5px;
    color: #9ca3af;
    padding: 6px 16px 10px;
    background: white;
    flex-shrink: 0;
}
.cb-powered span { color: var(--cb-gold); font-weight: 700; }
 
@media (max-width: 420px) {
    .cb-window { right: 12px; width: calc(100vw - 24px); bottom: 90px; }
    .cb-fab    { right: 14px; bottom: 18px; }
}

    .rh-slider-wrap {
        position: relative;
        width: 100%;
        height: 580px;
        margin-bottom: 44px;
        margin-top: 0;
        overflow: hidden;
        box-shadow: 0 40px 90px rgba(0, 0, 0, .25);
        isolation: isolate;
    }

    /* ═══════════════════════════════════════════
   SLIDE BASE
═══════════════════════════════════════════ */
    .rh-slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        pointer-events: none;
        transition: opacity 850ms cubic-bezier(.4, 0, .2, 1);
        z-index: 1;
        overflow: hidden;
        display: flex;
        align-items: center;
    }

    .rh-slide.active {
        opacity: 1;
        pointer-events: auto;
        z-index: 2;
    }

    .rh-slide.leaving {
        opacity: 0;
        z-index: 3;
    }

    /* ═══════════════════════════════════════════
   GRADIENT BACKGROUNDS — each slide unique
═══════════════════════════════════════════ */
    .rh-slide-1 {
        background:
            radial-gradient(ellipse 80% 100% at 70% 50%, #0f2027 0%, transparent 70%),
            linear-gradient(135deg, #0f2027 0%, #203a43 40%, #2c5364 100%);
    }

    .rh-slide-2 {
        background:
            radial-gradient(ellipse 80% 100% at 70% 50%, #582606 0%, transparent 70%),
            linear-gradient(135deg, #562b02 0%, #582e06 40%, #a75e1a 100%);
    }

    .rh-slide-3 {
        background:
            radial-gradient(ellipse 80% 100% at 70% 50%, #0c1445 0%, transparent 70%),
            linear-gradient(135deg, #060d39 0%, #05143d 40%, #003d59 100%);
    }

    .rh-slide-4 {
        background:
            radial-gradient(ellipse 80% 100% at 70% 50%, #052e16 0%, transparent 70%),
            linear-gradient(135deg, #052e16 0%, #023c2b 40%, #06835a 100%);
    }

    /* ═══════════════════════════════════════════
   MESH / NOISE TEXTURE OVERLAY
═══════════════════════════════════════════ */
    .sl-mesh {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255, 255, 255, .03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, .03) 1px, transparent 1px);
        background-size: 48px 48px;
        pointer-events: none;
        z-index: 1;
    }

    /* Glowing orbs */
    .sl-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        pointer-events: none;
        z-index: 1;
        animation: orbPulse 8s ease-in-out infinite;
    }

    .sl-orb-1 {
        width: 500px;
        height: 500px;
        top: -180px;
        right: -100px;
        opacity: .18;
    }

    .sl-orb-2 {
        width: 360px;
        height: 360px;
        bottom: -140px;
        left: 80px;
        opacity: .12;
        animation-delay: 1.5s;
    }

    @keyframes orbPulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.18);
        }
    }

    .rh-slide-1 .sl-orb-1 {
        background: #38bdf8;
    }

    .rh-slide-1 .sl-orb-2 {
        background: #06b6d4;
    }

    .rh-slide-2 .sl-orb-1 {
        background: #7c4008;
    }

    .rh-slide-2 .sl-orb-2 {
        background: #ce820f;
    }

    .rh-slide-3 .sl-orb-1 {
        background: #60a5fa;
    }

    .rh-slide-3 .sl-orb-2 {
        background: #38bdf8;
    }

    .rh-slide-4 .sl-orb-1 {
        background: #34d399;
    }

    .rh-slide-4 .sl-orb-2 {
        background: #a3e635;
    }

    /* Floating particles */
    .sl-particles {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 1;
        overflow: hidden;
    }

    .sl-p {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, .15);
        animation: pDrift linear infinite;
    }

    .sl-p:nth-child(1) {
        width: 6px;
        height: 6px;
        left: 8%;
        bottom: -10px;
        animation-duration: 7s;
        animation-delay: 0s;
    }

    .sl-p:nth-child(2) {
        width: 4px;
        height: 4px;
        left: 20%;
        bottom: -10px;
        animation-duration: 9.5s;
        animation-delay: 1s;
    }

    .sl-p:nth-child(3) {
        width: 8px;
        height: 8px;
        left: 35%;
        bottom: -10px;
        animation-duration: 6s;
        animation-delay: .4s;
    }

    .sl-p:nth-child(4) {
        width: 5px;
        height: 5px;
        left: 52%;
        bottom: -10px;
        animation-duration: 8s;
        animation-delay: 2.2s;
    }

    .sl-p:nth-child(5) {
        width: 7px;
        height: 7px;
        left: 68%;
        bottom: -10px;
        animation-duration: 7.5s;
        animation-delay: .9s;
    }

    .sl-p:nth-child(6) {
        width: 3px;
        height: 3px;
        left: 80%;
        bottom: -10px;
        animation-duration: 10s;
        animation-delay: 1.7s;
    }

    .sl-p:nth-child(7) {
        width: 5px;
        height: 5px;
        left: 92%;
        bottom: -10px;
        animation-duration: 6.5s;
        animation-delay: 3s;
    }

    @keyframes pDrift {
        0% {
            transform: translateY(0) rotate(0deg);
            opacity: .7;
        }

        100% {
            transform: translateY(-620px) rotate(360deg);
            opacity: 0;
        }
    }

    /* Left accent bar */
    .sl-accent {
        position: absolute;
        left: 0;
        top: 12%;
        bottom: 12%;
        width: 5px;
        border-radius: 0 4px 4px 0;
        z-index: 4;
    }


    /* ═══════════════════════════════════════════
   LAYOUT — two columns
═══════════════════════════════════════════ */
    .sl-layout {
        position: relative;
        z-index: 5;
        width: 100%;
        height: 100%;
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 56px;
        gap: 0;
    }

    /* ═══════════════════════════════════════════
   LEFT — TEXT CONTENT
═══════════════════════════════════════════ */
    .sl-text {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding-right: 32px;
    }

    .sl-counter {
        font-family: 'Outfit', sans-serif;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .2em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .45);
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sl-counter strong {
        font-size: 14px;
        color: rgba(255, 255, 255, .85);
        font-weight: 900;
    }

    .sl-counter-line {
        flex: 0 0 32px;
        height: 1px;
        background: rgba(255, 255, 255, .3);
    }

    .sl-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: 1px solid rgba(255, 255, 255, .3);
        color: rgba(255, 255, 255, .9);
        font-family: 'DM Sans', sans-serif;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        padding: 5px 14px;
        border-radius: 3px;
        margin-bottom: 20px;
        backdrop-filter: blur(8px);
        background: rgba(255, 255, 255, .07);
        width: fit-content;
        animation: textSlide 800ms .1s both;
    }

    .sl-badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        animation: dotBlink 1.5s ease-in-out infinite;
    }

    @keyframes dotBlink {

        0%,
        100% {
            transform: scale(1);
            opacity: 1
        }

        50% {
            transform: scale(1.6);
            opacity: .5
        }
    }

    .rh-slide-1 .sl-badge-dot {
        background: #38bdf8;
    }

    .rh-slide-2 .sl-badge-dot {
        background: #ce831a;
    }

    .rh-slide-3 .sl-badge-dot {
        background: #60a5fa;
    }

    .rh-slide-4 .sl-badge-dot {
        background: #34d399;
    }

    /* Giant BG word (like GREENADE!) */
    .sl-big-word {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        font-size: clamp(4rem, 7.5vw, 7.5rem);
        line-height: .88;
        letter-spacing: -.04em;
        color: transparent;
        -webkit-text-stroke: 2px rgba(255, 255, 255, .18);
        display: block;
        margin-bottom: 2px;
        animation: textSlide 800ms .15s both;
    }

    .sl-headline-main {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        font-size: clamp(1.8rem, 3.2vw, 3rem);
        color: white;
        line-height: 1.05;
        letter-spacing: -.025em;
        margin-bottom: 4px;
        animation: textSlide 800ms .22s both;
    }

    .sl-headline-sub {
        font-family: 'Outfit', sans-serif;
        font-weight: 400;
        font-size: clamp(1rem, 1.8vw, 1.4rem);
        color: rgba(255, 255, 255, .65);
        margin-bottom: 18px;
        animation: textSlide 800ms .28s both;
    }

    .sl-desc {
        font-family: 'DM Sans', sans-serif;
        font-size: 14.5px;
        color: rgba(255, 255, 255, .6);
        line-height: 1.65;
        max-width: 400px;
        margin-bottom: 32px;
        animation: textSlide 800ms .34s both;
    }

    .sl-actions {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        animation: textSlide 800ms .40s both;
    }

    @keyframes textSlide {
        from {
            opacity: 0;
            transform: translateX(-24px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .rh-slide:not(.active) .sl-text>* {
        animation: none;
    }

    .sl-btn-main {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        background: #ff9900;
        color: #131921;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 14px;
        padding: 14px 28px;
        border-radius: 7px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: transform .15s, box-shadow .15s, background .15s;
        box-shadow: 0 10px 30px rgba(255, 153, 0, .4);
        letter-spacing: .01em;
        white-space: nowrap;
    }

    .sl-btn-main:hover {
        background: #e68900;
        transform: translateY(-3px);
        box-shadow: 0 16px 40px rgba(255, 153, 0, .5);
    }

    .sl-btn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: rgba(255, 255, 255, .8);
        font-family: 'DM Sans', sans-serif;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        border: none;
        background: none;
        cursor: pointer;
        padding: 6px 0;
        border-bottom: 1px solid rgba(255, 255, 255, .3);
        transition: color .15s, border-color .15s;
        white-space: nowrap;
    }

    .sl-btn-ghost:hover {
        color: white;
        border-color: white;
    }

    .sl-btn-ghost i {
        font-size: 11px;
        transition: transform .15s;
    }

    .sl-btn-ghost:hover i {
        transform: translateX(4px);
    }

    /* ═══════════════════════════════════════════
   RIGHT — 3D PRODUCT IMAGE (large, floating)
═══════════════════════════════════════════ */
    .sl-visual {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        position: relative;
        perspective: 1000px;
        overflow: visible;
    }

    /* The 3D scene wrapper that parallax JS moves */
    .sl-img-layer {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        will-change: transform;
    }

    /* Main product image — large, with 3D shadow/depth */
    .sl-prod-img {
        width: 70%;
        height: auto;
        object-fit: contain;
        object-position: center;
        display: block;
        position: relative;
        z-index: 3;
        filter:
            drop-shadow(-30px 40px 60px rgba(0, 0, 0, .55)) drop-shadow(0 0 40px rgba(255, 255, 255, .08));
        animation: prodFloat 5s ease-in-out infinite;
        transform-origin: center bottom;
    }

    @keyframes prodFloat {

        0%,
        100% {
            transform: translateY(0) rotateY(-8deg) rotateX(3deg);
        }

        50% {
            transform: translateY(-18px) rotateY(8deg) rotateX(-2deg);
        }
    }

    .rh-slide:hover .sl-prod-img {
        animation-play-state: paused;
    }

    /* Glow circle behind product */
    .sl-prod-glow {
        position: absolute;
        width: 340px;
        height: 340px;
        border-radius: 50%;
        filter: blur(60px);
        opacity: .3;
        z-index: 2;
        animation: glowPulse 5s ease-in-out infinite;
    }

    @keyframes glowPulse {

        0%,
        100% {
            transform: scale(1);
            opacity: .3;
        }

        50% {
            transform: scale(1.15);
            opacity: .45;
        }
    }

    .rh-slide-1 .sl-prod-glow {
        background: radial-gradient(#38bdf8, #0ea5e9);
    }

    .rh-slide-2 .sl-prod-glow {
        background: radial-gradient(#c084fc, #cf8230);
    }

    .rh-slide-3 .sl-prod-glow {
        background: radial-gradient(#60a5fa, #3b82f6);
    }

    .rh-slide-4 .sl-prod-glow {
        background: radial-gradient(#34d399, #059669);
    }

    /* Ground shadow ellipse */
    .sl-prod-shadow {
        position: absolute;
        bottom: 60px;
        width: 260px;
        height: 30px;
        background: rgba(0, 0, 0, .45);
        border-radius: 50%;
        filter: blur(20px);
        z-index: 1;
        animation: shadowPulse 5s ease-in-out infinite;
    }

    @keyframes shadowPulse {

        0%,
        100% {
            transform: scaleX(1);
            opacity: .45;
        }

        50% {
            transform: scaleX(.75);
            opacity: .25;
        }
    }

    /* Floating price/info pill near product */
    .sl-info-pill {
        position: absolute;
        bottom: 80px;
        right: 10px;
        background: rgba(255, 255, 255, .1);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, .2);
        border-radius: 14px;
        padding: 12px 18px;
        z-index: 10;
        animation: pillFloat 4s ease-in-out infinite .8s;
        box-shadow: 0 12px 32px rgba(0, 0, 0, .25);
    }

    @keyframes pillFloat {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-8px);
        }
    }

    .sl-info-pill-price {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        font-size: 22px;
        color: #ff9900;
        display: block;
        line-height: 1;
    }

    .sl-info-pill-name {
        font-family: 'DM Sans', sans-serif;
        font-size: 11.5px;
        color: rgba(255, 255, 255, .7);
        margin-top: 3px;
        display: block;
    }

    /* Discount badge floating top-left of visual */
    .sl-disc-badge {
        position: absolute;
        top: 60px;
        left: 20px;
        background: #ff9900;
        color: #131921;
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        font-size: 14px;
        border-radius: 50%;
        width: 58px;
        height: 58px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        line-height: 1.1;
        box-shadow: 0 8px 24px rgba(255, 153, 0, .5);
        z-index: 10;
        animation: badgeSpin 6s linear infinite;
    }

    @keyframes badgeSpin {

        0%,
        100% {
            transform: rotate(-8deg) scale(1);
        }

        50% {
            transform: rotate(8deg) scale(1.08);
        }
    }

    .sl-disc-badge span {
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .05em;
    }

    /* ═══════════════════════════════════════════
   CONTROLS
═══════════════════════════════════════════ */
    /* Vertical dots — right edge */
    .sl-dots-v {
        position: absolute;
        right: 22px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 20;
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: center;
    }

    .sl-dot-v {
        width: 3px;
        height: 22px;
        border-radius: 2px;
        background: rgba(255, 255, 255, .25);
        border: none;
        padding: 0;
        cursor: pointer;
        transition: height .3s, background .3s;
    }

    .sl-dot-v.active {
        height: 42px;
        background: #ff9900;
    }

    /* Arrows — bottom left */
    .sl-arrows {
        position: absolute;
        bottom: 28px;
        left: 56px;
        z-index: 20;
        display: flex;
        gap: 10px;
    }

    .sl-arrow {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .1);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, .22);
        color: white;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .15s, transform .15s;
    }

    .sl-arrow:hover {
        background: rgba(255, 255, 255, .22);
        transform: scale(1.1);
    }

    /* Slide counter bottom-right */
    .sl-num {
        position: absolute;
        bottom: 34px;
        right: 56px;
        z-index: 20;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 11px;
        letter-spacing: .12em;
        color: rgba(255, 255, 255, .35);
        display: flex;
        align-items: baseline;
        gap: 5px;
    }

    .sl-num-cur {
        font-size: 24px;
        color: rgba(255, 255, 255, .8);
        font-weight: 900;
        line-height: 1;
    }

    /* Progress bar */
    .sl-prog-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: rgba(255, 255, 255, .1);
        z-index: 20;
    }

    .sl-prog-fill {
        height: 100%;
        width: 0%;
        border-radius: 0 2px 2px 0;
    }


    .sl-mouse {
        width: 18px;
        height: 28px;
        border: 1.5px solid rgba(255, 255, 255, .5);
        border-radius: 9px;
        display: flex;
        justify-content: center;
        padding-top: 5px;
    }

    .sl-mouse::after {
        content: '';
        width: 2px;
        height: 6px;
        background: white;
        border-radius: 2px;
    }

    /* ═══════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════ */
    @media (max-width: 960px) {
        .rh-slider-wrap {
            height: 500px;
        }

        .sl-layout {
            padding: 0 32px;
        }

        .sl-prod-img {
            width: 300px;
            height: 300px;
        }

        .sl-prod-glow {
            width: 260px;
            height: 260px;
        }

        .sl-big-word {
            font-size: 5rem;
        }
    }

    @media (max-width: 700px) {
        .rh-slider-wrap {
            height: 460px;
            border-radius: 0 0 20px 20px;
        }

        .sl-layout {
            grid-template-columns: 1fr;
            padding: 0 24px;
        }

        .sl-visual {
            display: none;
        }

        .sl-big-word {
            font-size: 4rem;
        }

        .sl-desc {
            font-size: 13.5px;
            margin-bottom: 20px;
        }

        .sl-arrows {
            left: 24px;
            bottom: 20px;
        }

        .sl-num {
            right: 28px;
        }

        .sl-dots-v {
            right: 12px;
        }

        .sl-scroll-hint {
            display: none;
        }
    }

    @media (max-width: 420px) {
        .rh-slider-wrap {
            height: 420px;
        }

        .sl-big-word {
            font-size: 3.2rem;
        }

        .sl-headline-main {
            font-size: 1.6rem;
        }

        .sl-btn-main,
        .sl-btn-ghost {
            font-size: 13px;
        }
    }

    .rh-slider-wrap {
        position: relative;
        width: 100%;
        height: 580px;
        margin-bottom: 44px;
        margin-top: 0;
        overflow: hidden;
        box-shadow: 0 40px 90px rgba(0, 0, 0, .25);
        isolation: isolate;
    }

    .rh-slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        pointer-events: none;
        transition: opacity 850ms cubic-bezier(.4, 0, .2, 1);
        z-index: 1;
        overflow: hidden;
        display: flex;
        align-items: center;
    }

    .rh-slide.active {
        opacity: 1;
        pointer-events: auto;
        z-index: 2;
    }

    .rh-slide.leaving {
        opacity: 0;
        z-index: 3;
    }

    .rh-slide-1 {
        background: radial-gradient(ellipse 80% 100% at 70% 50%, #0f2027 0%, transparent 70%), linear-gradient(135deg, #0f2027 0%, #203a43 40%, #2c5364 100%);
    }

    .rh-slide-2 {
        background: radial-gradient(ellipse 80% 100% at 70% 50%, #582606 0%, transparent 70%), linear-gradient(135deg, #562b02 0%, #582e06 40%, #a75e1a 100%);
    }

    .rh-slide-3 {
        background: radial-gradient(ellipse 80% 100% at 70% 50%, #0c1445 0%, transparent 70%), linear-gradient(135deg, #060d39 0%, #05143d 40%, #003d59 100%);
    }

    .rh-slide-4 {
        background: radial-gradient(ellipse 80% 100% at 70% 50%, #052e16 0%, transparent 70%), linear-gradient(135deg, #052e16 0%, #023c2b 40%, #06835a 100%);
    }

    .sl-mesh {
        position: absolute;
        inset: 0;
        background-image: linear-gradient(rgba(255, 255, 255, .03) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, .03) 1px, transparent 1px);
        background-size: 48px 48px;
        pointer-events: none;
        z-index: 1;
    }

    .sl-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        pointer-events: none;
        z-index: 1;
        animation: orbPulse 8s ease-in-out infinite;
    }

    .sl-orb-1 {
        width: 500px;
        height: 500px;
        top: -180px;
        right: -100px;
        opacity: .18;
    }

    .sl-orb-2 {
        width: 360px;
        height: 360px;
        bottom: -140px;
        left: 80px;
        opacity: .12;
        animation-delay: 1.5s;
    }

    @keyframes orbPulse {

        0%,
        100% {
            transform: scale(1)
        }

        50% {
            transform: scale(1.18)
        }
    }

    .rh-slide-1 .sl-orb-1 {
        background: #38bdf8
    }

    .rh-slide-1 .sl-orb-2 {
        background: #06b6d4
    }

    .rh-slide-2 .sl-orb-1 {
        background: #7c4008
    }

    .rh-slide-2 .sl-orb-2 {
        background: #ce820f
    }

    .rh-slide-3 .sl-orb-1 {
        background: #60a5fa
    }

    .rh-slide-3 .sl-orb-2 {
        background: #38bdf8
    }

    .rh-slide-4 .sl-orb-1 {
        background: #34d399
    }

    .rh-slide-4 .sl-orb-2 {
        background: #a3e635
    }

    .sl-particles {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 1;
        overflow: hidden;
    }

    .sl-p {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, .15);
        animation: pDrift linear infinite;
    }

    .sl-p:nth-child(1) {
        width: 6px;
        height: 6px;
        left: 8%;
        bottom: -10px;
        animation-duration: 7s
    }

    .sl-p:nth-child(2) {
        width: 4px;
        height: 4px;
        left: 20%;
        bottom: -10px;
        animation-duration: 9.5s;
        animation-delay: 1s
    }

    .sl-p:nth-child(3) {
        width: 8px;
        height: 8px;
        left: 35%;
        bottom: -10px;
        animation-duration: 6s;
        animation-delay: .4s
    }

    .sl-p:nth-child(4) {
        width: 5px;
        height: 5px;
        left: 52%;
        bottom: -10px;
        animation-duration: 8s;
        animation-delay: 2.2s
    }

    .sl-p:nth-child(5) {
        width: 7px;
        height: 7px;
        left: 68%;
        bottom: -10px;
        animation-duration: 7.5s;
        animation-delay: .9s
    }

    .sl-p:nth-child(6) {
        width: 3px;
        height: 3px;
        left: 80%;
        bottom: -10px;
        animation-duration: 10s;
        animation-delay: 1.7s
    }

    .sl-p:nth-child(7) {
        width: 5px;
        height: 5px;
        left: 92%;
        bottom: -10px;
        animation-duration: 6.5s;
        animation-delay: 3s
    }

    @keyframes pDrift {
        0% {
            transform: translateY(0) rotate(0deg);
            opacity: .7
        }

        100% {
            transform: translateY(-620px) rotate(360deg);
            opacity: 0
        }
    }

    .sl-accent {
        position: absolute;
        left: 0;
        top: 12%;
        bottom: 12%;
        width: 5px;
        border-radius: 0 4px 4px 0;
        z-index: 4;
    }

    .sl-layout {
        position: relative;
        z-index: 5;
        width: 100%;
        height: 100%;
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 56px;
        gap: 0;
    }

    .sl-text {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding-right: 32px;
    }

    .sl-counter {
        font-family: 'Outfit', sans-serif;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .2em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .45);
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sl-counter strong {
        font-size: 14px;
        color: rgba(255, 255, 255, .85);
        font-weight: 900;
    }

    .sl-counter-line {
        flex: 0 0 32px;
        height: 1px;
        background: rgba(255, 255, 255, .3);
    }

    .sl-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: 1px solid rgba(255, 255, 255, .3);
        color: rgba(255, 255, 255, .9);
        font-family: 'DM Sans', sans-serif;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        padding: 5px 14px;
        border-radius: 3px;
        margin-bottom: 20px;
        backdrop-filter: blur(8px);
        background: rgba(255, 255, 255, .07);
        width: fit-content;
        animation: textSlide 800ms .1s both;
    }

    .sl-badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        animation: dotBlink 1.5s ease-in-out infinite;
    }

    @keyframes dotBlink {

        0%,
        100% {
            transform: scale(1);
            opacity: 1
        }

        50% {
            transform: scale(1.6);
            opacity: .5
        }
    }

    .rh-slide-1 .sl-badge-dot {
        background: #38bdf8
    }

    .rh-slide-2 .sl-badge-dot {
        background: #ce831a
    }

    .rh-slide-3 .sl-badge-dot {
        background: #60a5fa
    }

    .rh-slide-4 .sl-badge-dot {
        background: #34d399
    }

    .sl-big-word {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        font-size: clamp(4rem, 7.5vw, 7.5rem);
        line-height: .88;
        letter-spacing: -.04em;
        color: transparent;
        -webkit-text-stroke: 2px rgba(255, 255, 255, .18);
        display: block;
        margin-bottom: 2px;
        animation: textSlide 800ms .15s both;
    }

    .sl-headline-main {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        font-size: clamp(1.8rem, 3.2vw, 3rem);
        color: white;
        line-height: 1.05;
        letter-spacing: -.025em;
        margin-bottom: 4px;
        animation: textSlide 800ms .22s both;
    }

    .sl-headline-sub {
        font-family: 'Outfit', sans-serif;
        font-weight: 400;
        font-size: clamp(1rem, 1.8vw, 1.4rem);
        color: rgba(255, 255, 255, .65);
        margin-bottom: 18px;
        animation: textSlide 800ms .28s both;
    }

    .sl-desc {
        font-family: 'DM Sans', sans-serif;
        font-size: 14.5px;
        color: rgba(255, 255, 255, .6);
        line-height: 1.65;
        max-width: 400px;
        margin-bottom: 32px;
        animation: textSlide 800ms .34s both;
    }

    .sl-actions {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        animation: textSlide 800ms .40s both;
    }

    @keyframes textSlide {
        from {
            opacity: 0;
            transform: translateX(-24px)
        }

        to {
            opacity: 1;
            transform: translateX(0)
        }
    }

    .rh-slide:not(.active) .sl-text>* {
        animation: none;
    }

    .sl-btn-main {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        background: #ff9900;
        color: #131921;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 14px;
        padding: 14px 28px;
        border-radius: 7px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: transform .15s, box-shadow .15s, background .15s;
        box-shadow: 0 10px 30px rgba(255, 153, 0, .4);
        letter-spacing: .01em;
        white-space: nowrap;
    }

    .sl-btn-main:hover {
        background: #e68900;
        transform: translateY(-3px);
        box-shadow: 0 16px 40px rgba(255, 153, 0, .5);
    }

    .sl-btn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: rgba(255, 255, 255, .8);
        font-family: 'DM Sans', sans-serif;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        border: none;
        background: none;
        cursor: pointer;
        padding: 6px 0;
        border-bottom: 1px solid rgba(255, 255, 255, .3);
        transition: color .15s, border-color .15s;
        white-space: nowrap;
    }

    .sl-btn-ghost:hover {
        color: white;
        border-color: white;
    }

    .sl-btn-ghost i {
        font-size: 11px;
        transition: transform .15s;
    }

    .sl-btn-ghost:hover i {
        transform: translateX(4px);
    }

    .sl-visual {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        position: relative;
        perspective: 1000px;
        overflow: visible;
    }

    .sl-img-layer {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        will-change: transform;
    }

    .sl-prod-img {
        width: 70%;
        height: auto;
        object-fit: contain;
        object-position: center;
        display: block;
        position: relative;
        z-index: 3;
        filter: drop-shadow(-30px 40px 60px rgba(0, 0, 0, .55)) drop-shadow(0 0 40px rgba(255, 255, 255, .08));
        animation: prodFloat 5s ease-in-out infinite;
        transform-origin: center bottom;
    }

    @keyframes prodFloat {

        0%,
        100% {
            transform: translateY(0) rotateY(-8deg) rotateX(3deg)
        }

        50% {
            transform: translateY(-18px) rotateY(8deg) rotateX(-2deg)
        }
    }

    .rh-slide:hover .sl-prod-img {
        animation-play-state: paused;
    }

    .sl-prod-glow {
        position: absolute;
        width: 340px;
        height: 340px;
        border-radius: 50%;
        filter: blur(60px);
        opacity: .3;
        z-index: 2;
        animation: glowPulse 5s ease-in-out infinite;
    }

    @keyframes glowPulse {

        0%,
        100% {
            transform: scale(1);
            opacity: .3
        }

        50% {
            transform: scale(1.15);
            opacity: .45
        }
    }

    .rh-slide-1 .sl-prod-glow {
        background: radial-gradient(#38bdf8, #0ea5e9)
    }

    .rh-slide-2 .sl-prod-glow {
        background: radial-gradient(#c084fc, #cf8230)
    }

    .rh-slide-3 .sl-prod-glow {
        background: radial-gradient(#60a5fa, #3b82f6)
    }

    .rh-slide-4 .sl-prod-glow {
        background: radial-gradient(#34d399, #059669)
    }

    .sl-prod-shadow {
        position: absolute;
        bottom: 60px;
        width: 260px;
        height: 30px;
        background: rgba(0, 0, 0, .45);
        border-radius: 50%;
        filter: blur(20px);
        z-index: 1;
        animation: shadowPulse 5s ease-in-out infinite;
    }

    @keyframes shadowPulse {

        0%,
        100% {
            transform: scaleX(1);
            opacity: .45
        }

        50% {
            transform: scaleX(.75);
            opacity: .25
        }
    }

    .sl-dots-v {
        position: absolute;
        right: 22px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 20;
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: center;
    }

    .sl-dot-v {
        width: 3px;
        height: 22px;
        border-radius: 2px;
        background: rgba(255, 255, 255, .25);
        border: none;
        padding: 0;
        cursor: pointer;
        transition: height .3s, background .3s;
    }

    .sl-dot-v.active {
        height: 42px;
        background: #ff9900;
    }

    .sl-arrows {
        position: absolute;
        bottom: 28px;
        left: 56px;
        z-index: 20;
        display: flex;
        gap: 10px;
    }

    .sl-arrow {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .1);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, .22);
        color: white;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .15s, transform .15s;
    }

    .sl-arrow:hover {
        background: rgba(255, 255, 255, .22);
        transform: scale(1.1);
    }

    .sl-num {
        position: absolute;
        bottom: 34px;
        right: 56px;
        z-index: 20;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 11px;
        letter-spacing: .12em;
        color: rgba(255, 255, 255, .35);
        display: flex;
        align-items: baseline;
        gap: 5px;
    }

    .sl-num-cur {
        font-size: 24px;
        color: rgba(255, 255, 255, .8);
        font-weight: 900;
        line-height: 1;
    }

    .sl-prog-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: rgba(255, 255, 255, .1);
        z-index: 20;
    }

    .sl-prog-fill {
        height: 100%;
        width: 0%;
        border-radius: 0 2px 2px 0;
    }

    @media(max-width:960px) {
        .rh-slider-wrap {
            height: 500px
        }

        .sl-layout {
            padding: 0 32px
        }

        .sl-prod-img {
            width: 300px;
            height: 300px
        }

        .sl-big-word {
            font-size: 5rem
        }
    }

    @media(max-width:700px) {
        .rh-slider-wrap {
            height: 460px
        }

        .sl-layout {
            grid-template-columns: 1fr;
            padding: 0 24px
        }

        .sl-visual {
            display: none
        }

        .sl-big-word {
            font-size: 4rem
        }

        .sl-arrows {
            left: 24px;
            bottom: 20px
        }

        .sl-num {
            right: 28px
        }

        .sl-dots-v {
            right: 12px
        }
    }

    @media(max-width:420px) {
        .rh-slider-wrap {
            height: 420px
        }

        .sl-big-word {
            font-size: 3.2rem
        }

        .sl-headline-main {
            font-size: 1.6rem
        }
    }

    /* ══════════════════════════════
       BEST SELLERS SECTION
    ══════════════════════════════ */
    .bs-section {
        max-width: 80rem;
        margin: 0 auto 4rem;
        padding: 0 3rem;
    }

    .bs-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .bs-title-group {}

    .bs-eyebrow {
        font-family: 'DM Sans', sans-serif;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: #ff9900;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }

    .bs-eyebrow::before {
        content: '';
        display: block;
        width: 28px;
        height: 2px;
        background: #ff9900;
        border-radius: 2px;
    }

    .bs-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        font-size: clamp(1.6rem, 3vw, 2.4rem);
        color: #0f172a;
        line-height: 1.1;
        letter-spacing: -.03em;
    }

    .bs-view-all {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 13.5px;
        color: #131921;
        text-decoration: none;
        border: 2px solid #131921;
        border-radius: 8px;
        padding: 10px 22px;
        transition: all .2s;
        white-space: nowrap;
    }

    .bs-view-all:hover {
        background: #131921;
        color: #ff9900;
    }

    .bs-view-all i {
        font-size: 11px;
        transition: transform .2s;
    }

    .bs-view-all:hover i {
        transform: translateX(4px);
    }

    /* Product grid */
    .bs-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
    }

    @media(max-width:1024px) {
        .bs-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width:560px) {
        .bs-grid {
            grid-template-columns: 1fr;
        }
    }

    .bs-card {
        background: #fff;
        border-radius: 18px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .05);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform .22s, box-shadow .22s;
        position: relative;
    }

    .bs-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, .12);
    }

    .bs-card-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 5;
        background: #131921;
        color: #ff9900;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 10px;
        letter-spacing: .1em;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .bs-card-badge i {
        font-size: 9px;
    }

    .bs-card-img-wrap {
        position: relative;
        width: 100%;
        aspect-ratio: 1/1;
        background: #f8fafc;
        overflow: hidden;
    }

    .bs-card-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: transform .4s cubic-bezier(.4, 0, .2, 1);
    }

    .bs-card:hover .bs-card-img {
        transform: scale(1.07);
    }

    .bs-card-overlay {
        position: absolute;
        inset: 0;
        background: rgba(19, 25, 33, .45);
        opacity: 0;
        transition: opacity .22s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .bs-card:hover .bs-card-overlay {
        opacity: 1;
    }

    .bs-overlay-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ff9900;
        color: #131921;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 12.5px;
        padding: 10px 18px;
        border-radius: 8px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transform: translateY(10px);
        transition: transform .22s .05s, background .15s;
        box-shadow: 0 4px 14px rgba(255, 153, 0, .4);
    }

    .bs-card:hover .bs-overlay-btn {
        transform: translateY(0);
    }

    .bs-overlay-btn:hover {
        background: #e68900;
    }

    .bs-card-body {
        padding: 1rem 1.1rem 1.2rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .bs-card-cat {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 4px;
    }

    .bs-card-name {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 15px;
        color: #0f172a;
        line-height: 1.3;
        margin-bottom: 2px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .bs-card-brand {
        font-size: 12px;
        color: #94a3b8;
        margin-bottom: 10px;
    }

    .bs-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: auto;
        padding-top: 10px;
        border-top: 1px solid #f1f5f9;
    }

    .bs-card-price {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        font-size: 17px;
        color: #131921;
    }

    .bs-card-stock {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 20px;
    }

    .bs-card-stock.in {
        background: #f0fdf4;
        color: #16a34a;
    }

    .bs-card-stock.low {
        background: #fff7ed;
        color: #c2410c;
    }

    .bs-card-stock.out {
        background: #fef2f2;
        color: #dc2626;
    }

    /* ══════════════════════════════
       BRAND LOGO SLIDER
    ══════════════════════════════ */
    .brand-section {
        background: #0f172a;
        padding: 3.5rem 0;
        overflow: hidden;
        position: relative;
    }

    .brand-section::before,
    .brand-section::after {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 120px;
        z-index: 5;
        pointer-events: none;
    }

    .brand-section::before {
        left: 0;
        background: linear-gradient(to right, #0f172a, transparent);
    }

    .brand-section::after {
        right: 0;
        background: linear-gradient(to left, #0f172a, transparent);
    }

    .brand-header {
        text-align: center;
        margin-bottom: 2.5rem;
        padding: 0 2rem;
    }

    .brand-eyebrow {
        font-family: 'DM Sans', sans-serif;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: #ff9900;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 8px;
    }

    .brand-eyebrow::before,
    .brand-eyebrow::after {
        content: '';
        display: block;
        width: 32px;
        height: 1px;
        background: rgba(255, 153, 0, .4);
    }

    .brand-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        font-size: clamp(1.4rem, 2.5vw, 2rem);
        color: white;
        letter-spacing: -.02em;
    }

    .brand-track-wrap {
        overflow: hidden;
        width: 100%;
    }

    .brand-track {
        display: flex;
        gap: 0;
        width: max-content;
        animation: brandScroll 28s linear infinite;
    }

    .brand-track:hover {
        animation-play-state: paused;
    }

    @keyframes brandScroll {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(-50%);
        }
    }

    .brand-item {
        flex-shrink: 0;
        width: 180px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 12px;
        background: rgba(255, 255, 255);
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 14px;
        transition: background .2s, border-color .2s, transform .2s;
        cursor: default;
    }

    .brand-item:hover {
        background: rgba(255, 153, 0, .1);
        border-color: rgba(255, 153, 0, .3);
        transform: translateY(-3px);
    }

    .brand-item-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }

    .brand-logo-icon {
        font-size: 26px;
        line-height: 1;
    }

    .brand-logo-name {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 13px;
        letter-spacing: .05em;
        color: rgba(0, 0, 0);
        text-transform: uppercase;
    }

    .brand-item:hover .brand-logo-name {
        color: #ff9900;
    }

    /* Feature cards */
    .feature-section {
        max-width: 80rem;
        margin: 0 auto 3rem;
        padding: 0 3rem;
    }
</style>

<!-- ══════════════════════════════════════════════════════════
     SLIDER HTML
══════════════════════════════════════════════════════════ -->
<div class="rh-slider-wrap" id="rhSlider" aria-label="Promotional slider">

    <!-- ══════════════════════════════
         SLIDE 1 — Electronics (dark teal)
    ══════════════════════════════ -->
    <div class="rh-slide rh-slide-1 active" role="group" aria-label="Slide 1">
        <div class="sl-mesh"></div>
        <div class="sl-orb sl-orb-1"></div>
        <div class="sl-orb sl-orb-2"></div>
        <div class="sl-particles">
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
        </div>
        <div class="sl-accent"></div>

        <div class="sl-layout">
            <!-- Text -->
            <div class="sl-text">
                <div class="sl-counter"><strong>01</strong><span class="sl-counter-line"></span><span>04</span></div>
                <div class="sl-badge"><span class="sl-badge-dot"></span> Flash Sale — Weekend Only</div>
                <span class="sl-big-word">TECH.</span>
                <h2 class="sl-headline-main">Next-Gen Electronics</h2>
                <p class="sl-headline-sub">Smartphones · Laptops · Audio</p>
                <p class="sl-desc">Top-brand gadgets at prices you won't find anywhere else. Free islandwide delivery on
                    all orders.</p>
                <div class="sl-actions">

                    <a href="index.php?page=products&sale=1" class="sl-btn-ghost">
                        Shop Now <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- 3D Product Visual -->
            <div class="sl-visual">
                <div class="sl-img-layer" data-parallax="1">
                    <div class="sl-prod-glow"></div>
                    <div class="sl-prod-shadow"></div>
                    <img class="sl-prod-img" src="../assets/images/1.png" alt="Smartphone 3D" loading="eager">

                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════
         SLIDE 2 — Fashion (deep purple)
    ══════════════════════════════ -->
    <div class="rh-slide rh-slide-2" role="group" aria-label="Slide 2">
        <div class="sl-mesh"></div>
        <div class="sl-orb sl-orb-1"></div>
        <div class="sl-orb sl-orb-2"></div>
        <div class="sl-particles">
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
        </div>
        <div class="sl-accent"></div>

        <div class="sl-layout">
            <div class="sl-text">
                <div class="sl-counter"><strong>02</strong><span class="sl-counter-line"></span><span>04</span></div>
                <div class="sl-badge"><span class="sl-badge-dot"></span> New Season Drop</div>
                <span class="sl-big-word">STYLE.</span>
                <h2 class="sl-headline-main">Fashion &amp; Footwear</h2>
                <p class="sl-headline-sub">Clothing · Shoes · Accessories</p>
                <p class="sl-desc">Curated styles for every occasion shipped to your door in 3 days. Up to 30% off new
                    arrivals.</p>
                <div class="sl-actions">

                    <a href="index.php?page=products&category=shoes" class="sl-btn-ghost">
                        Shop Fashion <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="sl-visual">
                <div class="sl-img-layer" data-parallax="2">
                    <div class="sl-prod-glow"></div>
                    <div class="sl-prod-shadow"></div>
                    <img class="sl-prod-img" src="../assets/images/2.png" alt="Sneaker 3D" loading="lazy">

                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════
         SLIDE 3 — Home (deep blue)
    ══════════════════════════════ -->
    <div class="rh-slide rh-slide-3" role="group" aria-label="Slide 3">
        <div class="sl-mesh"></div>
        <div class="sl-orb sl-orb-1"></div>
        <div class="sl-orb sl-orb-2"></div>
        <div class="sl-particles">
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
        </div>
        <div class="sl-accent"></div>

        <div class="sl-layout">
            <div class="sl-text">
                <div class="sl-counter"><strong>03</strong><span class="sl-counter-line"></span><span>04</span></div>
                <div class="sl-badge"><span class="sl-badge-dot"></span> New Collection Arrived</div>
                <span class="sl-big-word">HOME.</span>
                <h2 class="sl-headline-main">Home &amp; Living</h2>
                <p class="sl-headline-sub">Decor · Kitchen · Smart Home</p>
                <p class="sl-desc">Transform your space with premium kitchenware &amp; décor at unbeatable prices.
                    Island wide delivery.</p>
                <div class="sl-actions">

                    <a href="index.php?page=products&category=kitchen" class="sl-btn-ghost">
                        Shop Home <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="sl-visual">
                <div class="sl-img-layer" data-parallax="3">
                    <div class="sl-prod-glow"></div>
                    <div class="sl-prod-shadow"></div>
                    <img class="sl-prod-img" src="../assets/images/3.png" alt="Home product 3D" loading="lazy"
                        style="filter: drop-shadow(-30px 40px 60px rgba(0,0,0,.6)) drop-shadow(0 0 40px rgba(96,165,250,.15)) hue-rotate(180deg) saturate(0.8);">

                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════
         SLIDE 4 — Sports (deep green)
    ══════════════════════════════ -->
    <div class="rh-slide rh-slide-4" role="group" aria-label="Slide 4">
        <div class="sl-mesh"></div>
        <div class="sl-orb sl-orb-1"></div>
        <div class="sl-orb sl-orb-2"></div>
        <div class="sl-particles">
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
            <div class="sl-p"></div>
        </div>
        <div class="sl-accent"></div>

        <div class="sl-layout">
            <div class="sl-text">
                <div class="sl-counter"><strong>04</strong><span class="sl-counter-line"></span><span>04</span></div>
                <div class="sl-badge"><span class="sl-badge-dot"></span> Level Up Your Game</div>
                <span class="sl-big-word">MOVE.</span>
                <h2 class="sl-headline-main">Sports &amp; Fitness</h2>
                <p class="sl-headline-sub">Equipment · Activewear · Gear</p>
                <p class="sl-desc">Top-rated sports gear for every workout level. Express delivery. 35% off all fitness
                    accessories.</p>
                <div class="sl-actions">

                    <a href="index.php?page=products&category=fitness" class="sl-btn-ghost">
                        Shop Sports <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="sl-visual">
                <div class="sl-img-layer" data-parallax="4">
                    <div class="sl-prod-glow"></div>
                    <div class="sl-prod-shadow"></div>
                    <img class="sl-prod-img" src="../assets/images/4.png" alt="Sneaker Sport 3D" loading="lazy">

                </div>
            </div>
        </div>
    </div>

    <!-- Controls -->
    <div class="sl-dots-v" role="tablist">
        <button class="sl-dot-v active" onclick="rhGoTo(0)" aria-label="Slide 1"></button>
        <button class="sl-dot-v" onclick="rhGoTo(1)" aria-label="Slide 2"></button>
        <button class="sl-dot-v" onclick="rhGoTo(2)" aria-label="Slide 3"></button>
        <button class="sl-dot-v" onclick="rhGoTo(3)" aria-label="Slide 4"></button>
    </div>

    <div class="sl-arrows">
        <button class="sl-arrow" onclick="rhSlide(-1)" aria-label="Previous"><i
                class="fa-solid fa-chevron-left"></i></button>
        <button class="sl-arrow" onclick="rhSlide(1)" aria-label="Next"><i
                class="fa-solid fa-chevron-right"></i></button>
    </div>

    <div class="sl-num">
        <span class="sl-num-cur" id="slNumCur">01</span>
        <span>/</span><span>04</span>
    </div>

    <div class="sl-prog-bar">
        <div class="sl-prog-fill" id="slProgFill"></div>
    </div>



</div><!-- /rh-slider-wrap -->

<!-- Feature cards -->
<section class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
    style="margin-top:8px; max-width: 80rem; margin: 0 auto 3rem; padding: 0 3rem;">
    <div
        class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-soft transition hover:-translate-y-1 hover:shadow-lg">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
            <i class="fa-solid fa-layer-group text-lg"></i>
        </div>
        <h3 class="mt-4 font-semibold text-slate-900">Rich product catalogue</h3>
        <p class="mt-2 text-sm leading-relaxed text-slate-600">Search by name or brand and browse responsive grids that
            look great on phone, tablet, or desktop.</p>
    </div>
    <div
        class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-soft transition hover:-translate-y-1 hover:shadow-lg">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
            <i class="fa-solid fa-cart-shopping text-lg"></i>
        </div>
        <h3 class="mt-4 font-semibold text-slate-900">Streamlined cart &amp; checkout</h3>
        <p class="mt-2 text-sm leading-relaxed text-slate-600">Update quantities instantly and complete your purchase
            with a guided, transparent checkout flow.</p>
    </div>
    <div
        class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-soft transition hover:-translate-y-1 hover:shadow-lg sm:col-span-2 lg:col-span-1">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
            <i class="fa-solid fa-life-ring text-lg"></i>
        </div>
        <h3 class="mt-4 font-semibold text-slate-900">Operations &amp; customer care</h3>
        <p class="mt-2 text-sm leading-relaxed text-slate-600">Support tickets, live order status, and inventory
            oversight in a unified retail operations hub.</p>
    </div>
</section>



<!-- ══ BEST SELLERS ══ -->
<section class="bs-section">
    <div class="bs-header">
        <div class="bs-title-group">
            <div class="bs-eyebrow">Best Sellers</div>
            <h2 class="bs-title">Our Most-Loved Products</h2>
        </div>
        <a href="index.php?page=products" class="bs-view-all">
            View All Products <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <?php if (empty($bestSellers)): ?>
        <p class="text-center text-slate-400 py-12">No products available yet.</p>
    <?php else: ?>
        <div class="bs-grid">
            <?php foreach ($bestSellers as $i => $p):
                $stock = (int) $p['stock_qty'];
                $imgSrc = !empty($p['image_url'])
                    ? htmlspecialchars(assetImageUrl($p['image_url']))
                    : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="400"%3E%3Crect width="400" height="400" fill="%23f1f5f9"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" font-size="64"%3E📦%3C/text%3E%3C/svg%3E';
                $medals = ['🥇', '🥈', '🥉', '⭐'];
                ?>
                <div class="bs-card" style="animation-delay:<?= $i * .08 ?>s">
                    <div class="bs-card-badge">
                        <i class="fa-solid fa-fire"></i>
                        <?= $i === 0 ? 'Best Seller' : 'Top Pick' ?>
                    </div>

                    <div class="bs-card-img-wrap">
                        <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="bs-card-img">
                        <div class="bs-card-overlay">
                            <a href="index.php?page=product&id=<?= $p['id'] ?>" class="bs-overlay-btn">
                                <i class="fa-solid fa-eye"></i> Quick View
                            </a>
                        </div>
                    </div>

                    <div class="bs-card-body">
                        <div class="bs-card-cat"><?= htmlspecialchars($p['category_name'] ?? 'General') ?></div>
                        <div class="bs-card-name"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="bs-card-brand"><?= htmlspecialchars($p['brand'] ?? '') ?></div>
                        <div class="bs-card-footer">
                            <div class="bs-card-price"><?= formatCurrency($p['price']) ?></div>
                            <?php if ($stock === 0): ?>
                                <span class="bs-card-stock out">Out of Stock</span>
                            <?php elseif ($stock <= 5): ?>
                                <span class="bs-card-stock low">Only <?= $stock ?> left</span>
                            <?php else: ?>
                                <span class="bs-card-stock in">In Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>


    <?php endif; ?>
</section>

<!-- ══ OUR BRANDS ══ -->
<section class="brand-section">
    <div class="brand-header">
        <div class="brand-eyebrow">Our Brands</div>
        <h2 class="brand-title">Trusted Names, Quality Products</h2>
    </div>

    <div class="brand-track-wrap">
        <div class="brand-track" id="brandTrack">
            <?php
            $brands = [
                ['icon' => '../assets/images/brands/1.png', 'name' => 'Apple'],
                ['icon' => '../assets/images/brands/2.png', 'name' => 'Samsung'],
                ['icon' => '../assets/images/brands/3.png', 'name' => 'Nike'],
                ['icon' => '../assets/images/brands/4.png', 'name' => 'Adidas'],
                ['icon' => '../assets/images/brands/5.png', 'name' => 'Sony'],
                ['icon' => '../assets/images/brands/6.png', 'name' => 'Bose'],
                ['icon' => '../assets/images/brands/7.png', 'name' => 'Casio'],
                ['icon' => '../assets/images/brands/8.png', 'name' => 'Canon'],
                ['icon' => '../assets/images/brands/9.png', 'name' => 'LG'],
            ];
            // Duplicate for seamless loop
            $allBrands = array_merge($brands, $brands);
            foreach ($allBrands as $brand): ?>
                <div class="brand-item">
                    <div class="brand-item-inner">
                        <span class="brand-logo-icon">
                            <?php if (strpos($brand['icon'], '.') !== false): ?>
                                <img src="<?= htmlspecialchars($brand['icon']) ?>" alt="<?= htmlspecialchars($brand['name']) ?>"
                                    style="height:28px; width:auto;">
                            <?php else: ?>
                                <?= $brand['icon'] ?>
                            <?php endif; ?>
                        </span>
                        <span class="brand-logo-name"><?= htmlspecialchars($brand['name']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>



<!-- ══════════════════════════════════════════════════════════
     SLIDER JS
══════════════════════════════════════════════════════════ -->
<script>
    (function () {
        'use strict';

        const slides = Array.from(document.querySelectorAll('.rh-slide'));
        const dotsV = Array.from(document.querySelectorAll('.sl-dot-v'));
        const numCur = document.getElementById('slNumCur');
        const progFill = document.getElementById('slProgFill');
        const wrap = document.getElementById('rhSlider');

        let cur = 0;
        let timer = null;
        let progRaf = null;
        let progStart = null;
        const INTERVAL = 5500;
        const PADS = ['01', '02', '03', '04'];

        // ── Transition ──
        function go(idx) {
            const prev = cur;
            cur = ((idx % slides.length) + slides.length) % slides.length;
            if (prev === cur) return;

            slides[prev].classList.remove('active');
            slides[prev].classList.add('leaving');
            setTimeout(() => slides[prev]?.classList.remove('leaving'), 1000);

            slides[cur].classList.add('active');
            dotsV.forEach(d => d.classList.remove('active'));
            dotsV[cur].classList.add('active');
            if (numCur) numCur.textContent = PADS[cur];

            applyParallax();
            startProgress();
        }

        window.rhSlide = function (dir) { clearTimer(); go(cur + dir); startTimer(); };
        window.rhGoTo = function (idx) { clearTimer(); go(idx); startTimer(); };

        function startTimer() { clearTimer(); timer = setInterval(() => go(cur + 1), INTERVAL); }
        function clearTimer() { clearInterval(timer); }

        wrap.addEventListener('mouseenter', clearTimer);
        wrap.addEventListener('mouseleave', startTimer);

        // ── Progress bar ──
        function startProgress() {
            cancelAnimationFrame(progRaf);
            if (!progFill) return;
            progFill.style.transition = 'none';
            progFill.style.width = '0%';
            progStart = performance.now();
            progRaf = requestAnimationFrame(function tick(now) {
                const pct = Math.min(((now - progStart) / INTERVAL) * 100, 100);
                progFill.style.width = pct + '%';
                if (pct < 100) progRaf = requestAnimationFrame(tick);
            });
        }

        // ── PARALLAX SCROLL ──
        // As user scrolls DOWN → image moves DOWN (same dir) = cinematic parallax
        // Also applies a mild 3D tilt based on scroll position
        let ticking = false;

        function applyParallax() {
            const rect = wrap.getBoundingClientRect();
            const vh = window.innerHeight;
            const slH = rect.height;

            // norm: 0 = slider centred in viewport, ±1 = slider edge at viewport edge
            const centre = rect.top + slH / 2;
            const norm = (centre - vh / 2) / (vh / 2 + slH / 2); // -1 to +1

            // Travel range for parallax: ±55px (image is 120% tall, enough headroom)
            const parallaxY = norm * 55;

            // 3D tilt: subtle X rotation (looks like the slide "rotates" as you scroll)
            const tiltX = norm * 5; // degrees

            slides.forEach((slide, i) => {
                const layer = slide.querySelector('[data-parallax]');
                if (!layer) return;
                const img = layer.querySelector('img');
                if (!layer || !img) return;

                if (i === cur) {
                    // Move entire visual layer for parallax
                    layer.style.transform = `translateY(${parallaxY}px)`;
                    // Add rotateX tilt on the image for the "rotating while scrolling" feel
                    const baseFilter = img.dataset.baseFilter || img.style.filter || '';
                    img.style.transform = `scale(1.04) rotateX(${tiltX}deg)`;
                } else {
                    layer.style.transform = 'translateY(0)';
                    img.style.transform = 'scale(1.04)';
                }
            });
        }

        window.addEventListener('scroll', () => {
            if (!ticking) {
                ticking = true;
                requestAnimationFrame(() => { applyParallax(); ticking = false; });
            }
        }, { passive: true });
        window.addEventListener('resize', applyParallax, { passive: true });

        // ── Swipe ──
        let tx = 0, ty = 0;
        wrap.addEventListener('touchstart', e => { tx = e.touches[0].clientX; ty = e.touches[0].clientY; }, { passive: true });
        wrap.addEventListener('touchend', e => {
            const dx = e.changedTouches[0].clientX - tx;
            const dy = e.changedTouches[0].clientY - ty;
            if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 44) rhSlide(dx < 0 ? 1 : -1);
        }, { passive: true });

        // ── Keyboard ──
        document.addEventListener('keydown', e => {
            if (e.key === 'ArrowLeft') rhSlide(-1);
            if (e.key === 'ArrowRight') rhSlide(1);
        });

        // ── Init ──
        startTimer();
        startProgress();
        applyParallax();
    })();


    (function () {
        'use strict';
        const slides = Array.from(document.querySelectorAll('.rh-slide'));
        const dotsV = Array.from(document.querySelectorAll('.sl-dot-v'));
        const numCur = document.getElementById('slNumCur');
        const progFill = document.getElementById('slProgFill');
        const wrap = document.getElementById('rhSlider');
        let cur = 0, timer = null, progRaf = null, progStart = null;
        const INTERVAL = 5500, PADS = ['01', '02', '03', '04'];

        function go(idx) {
            const prev = cur;
            cur = ((idx % slides.length) + slides.length) % slides.length;
            if (prev === cur) return;
            slides[prev].classList.remove('active'); slides[prev].classList.add('leaving');
            setTimeout(() => slides[prev]?.classList.remove('leaving'), 1000);
            slides[cur].classList.add('active');
            dotsV.forEach(d => d.classList.remove('active')); dotsV[cur].classList.add('active');
            if (numCur) numCur.textContent = PADS[cur];
            applyParallax(); startProgress();
        }
        window.rhSlide = function (dir) { clearTimer(); go(cur + dir); startTimer(); };
        window.rhGoTo = function (idx) { clearTimer(); go(idx); startTimer(); };
        function startTimer() { clearTimer(); timer = setInterval(() => go(cur + 1), INTERVAL); }
        function clearTimer() { clearInterval(timer); }
        wrap.addEventListener('mouseenter', clearTimer);
        wrap.addEventListener('mouseleave', startTimer);

        function startProgress() {
            cancelAnimationFrame(progRaf); if (!progFill) return;
            progFill.style.transition = 'none'; progFill.style.width = '0%';
            progStart = performance.now();
            progRaf = requestAnimationFrame(function tick(now) {
                const pct = Math.min(((now - progStart) / INTERVAL) * 100, 100);
                progFill.style.width = pct + '%';
                if (pct < 100) progRaf = requestAnimationFrame(tick);
            });
        }

        let ticking = false;
        function applyParallax() {
            const rect = wrap.getBoundingClientRect(), vh = window.innerHeight, slH = rect.height;
            const centre = rect.top + slH / 2, norm = (centre - vh / 2) / (vh / 2 + slH / 2);
            const parallaxY = norm * 55, tiltX = norm * 5;
            slides.forEach((slide, i) => {
                const layer = slide.querySelector('[data-parallax]'); if (!layer) return;
                const img = layer.querySelector('img'); if (!img) return;
                if (i === cur) { layer.style.transform = `translateY(${parallaxY}px)`; img.style.transform = `scale(1.04) rotateX(${tiltX}deg)`; }
                else { layer.style.transform = 'translateY(0)'; img.style.transform = 'scale(1.04)'; }
            });
        }
        window.addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(() => { applyParallax(); ticking = false; }); } }, { passive: true });
        window.addEventListener('resize', applyParallax, { passive: true });

        let tx = 0, ty = 0;
        wrap.addEventListener('touchstart', e => { tx = e.touches[0].clientX; ty = e.touches[0].clientY; }, { passive: true });
        wrap.addEventListener('touchend', e => { const dx = e.changedTouches[0].clientX - tx, dy = e.changedTouches[0].clientY - ty; if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 44) rhSlide(dx < 0 ? 1 : -1); }, { passive: true });
        document.addEventListener('keydown', e => { if (e.key === 'ArrowLeft') rhSlide(-1); if (e.key === 'ArrowRight') rhSlide(1); });

        startTimer(); startProgress(); applyParallax();
    })();


    (function () {
    'use strict';
 
    // ── State ──
    let isOpen    = false;
    let isWaiting = false;
    let history   = [];  // [{role:'user'|'assistant', content:'...'}]
 
    const fab      = document.getElementById('cbFab');
    const win      = document.getElementById('cbWindow');
    const msgs     = document.getElementById('cbMessages');
    const input    = document.getElementById('cbInput');
    const sendBtn  = document.getElementById('cbSend');
    const notif    = document.getElementById('cbNotif');
    const chips    = document.getElementById('cbChips');
 
    // ── Welcome message ──
    const welcomeText = "👋 Hi there! I'm **Rita**, your RetailHub shopping assistant.\n\nI can help you with orders, delivery, returns, payments, and finding great products. What can I help you with today?";
    addBotMsg(welcomeText);
 
    // ── Toggle ──
    window.cbToggle = function () {
        isOpen = !isOpen;
        win.classList.toggle('open', isOpen);
        fab.classList.toggle('open', isOpen);
        if (isOpen) {
            notif.style.display = 'none';
            setTimeout(() => input.focus(), 300);
        }
    };
 
    // ── Quick chips ──
    window.cbQuick = function (text) {
        chips.style.display = 'none';
        sendMessage(text);
    };
 
    // ── Keyboard ──
    window.cbKeydown = function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            cbSend();
        }
    };
 
    // ── Auto-resize textarea ──
    window.cbAutoResize = function (el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 100) + 'px';
    };
 
    // ── Send ──
    window.cbSend = function () {
        const text = input.value.trim();
        if (!text || isWaiting) return;
        input.value = '';
        input.style.height = 'auto';
        sendMessage(text);
    };
 
    function sendMessage(text) {
        if (isWaiting) return;
 
        // Render user bubble
        addUserMsg(text);
        history.push({ role: 'user', content: text });
 
        // Show typing
        isWaiting = true;
        sendBtn.disabled = true;
        const typingEl = addTyping();
 
        // POST to server
        const formData = new FormData();
        formData.append('chatbot_message', text);
        formData.append('history', JSON.stringify(history.slice(-10))); // last 10 turns context
 
fetch('/RetailHub/public/ajax/chatbot.php', { method: 'POST', body: formData })

            .then(r => r.json())
            .then(data => {
                typingEl.remove();
                const reply = data.reply || 'Sorry, I couldn\'t get a response. Please try again.';
                addBotMsg(reply);
                history.push({ role: 'assistant', content: reply });
            })
            .catch(() => {
                typingEl.remove();
                addBotMsg('❌ Connection error. Please check your internet and try again.');
            })
            .finally(() => {
                isWaiting = false;
                sendBtn.disabled = false;
                input.focus();
            });
    }
 
    // ── Render helpers ──
    function addUserMsg(text) {
        const userInitial = (<?= json_encode(isLoggedIn() ? strtoupper(mb_substr(currentUser()['full_name'] ?? 'U', 0, 1)) : 'U') ?>);
        const el = document.createElement('div');
        el.className = 'cb-msg user';
        el.innerHTML = `
            <div class="cb-msg-avatar">${userInitial}</div>
            <div class="cb-bubble">${escHtml(text)}</div>
        `;
        msgs.appendChild(el);
        scrollBottom();
    }
 
    function addBotMsg(text) {
        const el = document.createElement('div');
        el.className = 'cb-msg bot';
        el.innerHTML = `
            <div class="cb-msg-avatar">🤖</div>
            <div class="cb-bubble">${renderMarkdown(text)}</div>
        `;
        msgs.appendChild(el);
        scrollBottom();
    }
 
    function addTyping() {
        const el = document.createElement('div');
        el.className = 'cb-msg bot cb-typing';
        el.innerHTML = `
            <div class="cb-msg-avatar">🤖</div>
            <div class="cb-typing-dots"><span></span><span></span><span></span></div>
        `;
        msgs.appendChild(el);
        scrollBottom();
        return el;
    }
 
    function scrollBottom() {
        msgs.scrollTop = msgs.scrollHeight;
    }
 
    // ── Simple markdown → HTML ──
    function renderMarkdown(text) {
        return escHtml(text)
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');
    }
 
    function escHtml(s) {
        return String(s)
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;');
    }
 
    // ── Show notif after 4s if not opened ──
    setTimeout(() => {
        if (!isOpen && notif) {
            notif.style.display = 'block';
        }
    }, 4000);
 
})();
</script>