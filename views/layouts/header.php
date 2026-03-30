<?php
require_once __DIR__ . '/../../includes/Database.php';

$pdo = Database::connection();

$categories = [];
try {
    $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

$cartCount = 0;
if (isLoggedIn()) {
    try {
        $cartStmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE user_id = ?");
        $cartStmt->execute([currentUser()['id']]);
        $cartCount = (int) $cartStmt->fetchColumn();
    } catch (Exception $e) {
    }
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ta', 'si'])) {
    $_SESSION['lang'] = $_GET['lang'];
    setcookie('retailhub_lang', $_GET['lang'], time() + 31536000, '/');
}
$lang = $_SESSION['lang'] ?? $_COOKIE['retailhub_lang'] ?? 'en';
if (!in_array($lang, ['en', 'ta', 'si']))
    $lang = 'en';

$t = [
    'en' => [
        'free_shipping' => 'Free shipping on orders over $50',
        'secure' => 'Secure & encrypted checkout',
        'support' => '24/7 Support',
        'deliver_to' => 'Deliver to',
        'select_location' => 'Select location',
        'all_categories' => 'All Categories',
        'search_ph' => 'Search products, brands and more...',
        'hello' => 'Hello,',
        'my_account' => 'My Account',
        'returns' => 'Returns &',
        'orders' => 'Orders',
        'cart' => 'Cart',
        'logout' => 'Logout',
        'sign_in' => 'Hello, sign in',
        'account' => 'Account',
        'sign_up' => 'Sign Up',
        'home' => 'Home',
        'all_products' => 'All Products',
        'my_orders' => 'My Orders',
        'admin' => 'Admin',
        'support_desk' => 'Support Desk',
        'shop_all' => 'Shop All',
        'dashboard' => 'Dashboard',
        'create_account' => 'Create Account',
        'premium_store' => 'PREMIUM STORE',
    ],
    'ta' => [
        'free_shipping' => '$50க்கு மேல் இலவச டெலிவரி',
        'secure' => 'பாதுகாப்பான பணம் செலுத்துதல்',
        'support' => '24/7 ஆதரவு',
        'deliver_to' => 'டெலிவரி இடம்',
        'select_location' => 'இடம் தேர்ந்தெடுக்கவும்',
        'all_categories' => 'அனைத்து வகைகள்',
        'search_ph' => 'தயாரிப்புகள், பிராண்டுகளை தேடுங்கள்...',
        'hello' => 'வணக்கம்,',
        'my_account' => 'என் கணக்கு',
        'returns' => 'திரும்பல் &',
        'orders' => 'ஆர்டர்கள்',
        'cart' => 'கார்ட்',
        'logout' => 'வெளியேறு',
        'sign_in' => 'வணக்கம், உள்நுழைக',
        'account' => 'கணக்கு',
        'sign_up' => 'பதிவு செய்க',
        'home' => 'முகப்பு',
        'all_products' => 'அனைத்து பொருட்கள்',
        'my_orders' => 'என் ஆர்டர்கள்',
        'admin' => 'நிர்வாகி',
        'support_desk' => 'ஆதரவு மேடை',
        'shop_all' => 'அனைத்தும் வாங்க',
        'dashboard' => 'டாஷ்போர்டு',
        'create_account' => 'கணக்கு உருவாக்கவும்',
        'premium_store' => 'பிரீமியம் ஸ்டோர்',
    ],
    'si' => [
        'free_shipping' => '$50 ට වැඩි ඇණවුම් නොමිලේ',
        'secure' => 'ආරක්ෂිත ගෙවීම',
        'support' => '24/7 සහාය',
        'deliver_to' => 'ලිපිනය',
        'select_location' => 'ස්ථානය තෝරන්න',
        'all_categories' => 'සියලු ප්‍රවර්ග',
        'search_ph' => 'නිෂ්පාදන, වෙළඳ නාම සොයන්න...',
        'hello' => 'හෙලෝ,',
        'my_account' => 'මගේ ගිණුම',
        'returns' => 'ආපසු &',
        'orders' => 'ඇණවුම්',
        'cart' => 'කාට්',
        'logout' => 'ඉවත් වන්න',
        'sign_in' => 'හෙලෝ, පිවිසෙන්න',
        'account' => 'ගිණුම',
        'sign_up' => 'ලියාපදිංචි',
        'home' => 'මුල් පිටුව',
        'all_products' => 'සියලු නිෂ්පාදන',
        'my_orders' => 'මගේ ඇණවුම්',
        'admin' => 'පරිපාලන',
        'support_desk' => 'සහාය මේසය',
        'shop_all' => 'සියල්ල මිලදී ගන්න',
        'dashboard' => 'උපකරණ පුවරුව',
        'create_account' => 'ගිණුමක් සාදන්න',
        'premium_store' => 'ප්‍රිමියම් ගබඩාව',
    ],
];
$_ = $t[$lang];

$currentCat = $_GET['category'] ?? '';
$currentQ = $_GET['q'] ?? '';
?>
<!doctype html>
<html lang="<?= $lang ?>" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RetailHub — Online retail store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&family=Noto+Sans+Sinhala:wght@400;600;700&family=Noto+Sans+Tamil:wght@400;600;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['DM Sans', 'Noto Sans Sinhala', 'Noto Sans Tamil', 'system-ui', 'sans-serif'],
                        display: ['Outfit', 'system-ui', 'sans-serif']
                    },
                    colors: {
                        brand: { 50: '#fff8e7', 100: '#ffefc0', 200: '#ffd966', 300: '#ffc107', 400: '#ffb300', 500: '#ff9900', 600: '#e67e00', 700: '#cc6600', 800: '#a35200', 900: '#131921' },
                        navy: { 900: '#131921', 800: '#1a2332', 700: '#232f3e', 600: '#37475a', 500: '#485769' }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        crossorigin="anonymous">

    <style>
        :root {
            --navy-deep: #131921;
            --navy-mid: #232f3e;
            --navy-light: #37475a;
            --gold: #ff9900;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', 'Noto Sans Sinhala', 'Noto Sans Tamil', system-ui, sans-serif;
            background: #f0f2f5;
            margin: 0;
        }

        /* ── Top bar ─────────────────────────── */
        .top-bar {
            background: var(--navy-deep);
            color: rgba(255, 255, 255, .75);
            font-size: 12px;
            letter-spacing: .02em;
        }

        .top-bar-inner {
            max-width: 1400px;
            margin: 0 auto;
            padding: 5px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .top-bar a {
            color: rgba(255, 255, 255, .75);
            text-decoration: none;
            transition: color .15s;
        }

        .top-bar a:hover {
            color: var(--gold);
        }

        /* ── Language switcher ───────────────── */
        .lang-switcher {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .lang-btn {
            display: flex;
            align-items: center;
            gap: 5px;
            background: none;
            border: 1px solid rgba(255, 255, 255, .2);
            color: rgba(255, 255, 255, .85);
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: all .15s;
        }

        .lang-btn:hover {
            border-color: var(--gold);
            color: var(--gold);
        }

        .lang-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            background: white;
            border-radius: 10px;
            min-width: 170px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .25);
            z-index: 10000;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .lang-dropdown.open {
            display: block;
            animation: modalPop .15s ease-out;
        }

        .lang-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 15px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            transition: background .12s;
            text-decoration: none;
        }

        .lang-option:hover {
            background: #fffbf2;
            color: #b45309;
        }

        .lang-option.active {
            background: #fff8e7;
            color: var(--gold);
        }

        .lang-option .lf {
            font-size: 17px;
        }

        .lang-option .lname {
            flex: 1;
        }

        .lang-option .lnative {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 400;
        }

        .lang-option.active .lnative {
            color: #f59e0b;
        }

        /* ── Main header ─────────────────────── */
        .main-header {
            background: var(--navy-mid);
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 2px 20px rgba(0, 0, 0, .4);
        }

        .header-inner {
            max-width: 1400px;
            margin: 0 auto;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* ── Logo ────────────────────────────── */
        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            padding: 6px 8px;
            border-radius: 6px;
            border: 2px solid transparent;
            transition: border-color .15s;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--gold) 0%, #ff6b00 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            color: var(--navy-deep);
            box-shadow: 0 0 0 3px rgba(255, 153, 0, .2);
            flex-shrink: 0;
        }

        .logo-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 19px;
            color: white;
            line-height: 1;
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }

        .logo-text span {
            color: var(--gold);
        }

        .logo-sub {
            font-size: 9px;
            font-weight: 500;
            color: var(--gold);
            letter-spacing: .08em;
            font-family: 'DM Sans', sans-serif;
        }

        /* ── Search bar ──────────────────────── */
        .search-wrap {
            display: flex;
            flex: 1;
            max-width: 780px;
            min-width: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 0 2px transparent;
            transition: box-shadow .15s;
            height: 44px;
        }

        .search-wrap:focus-within {
            box-shadow: 0 0 0 3px var(--gold);
        }

        .search-category {
            appearance: none;
            -webkit-appearance: none;
            background: #e8e0d0 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23666'/%3E%3C/svg%3E") no-repeat right 10px center;
            border: none;
            padding: 0 28px 0 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 12.5px;
            font-weight: 600;
            color: #333;
            cursor: pointer;
            width: 140px;
            max-width: 140px;
            min-width: 100px;
            border-right: 1px solid #ccc;
            outline: none;
            transition: background .15s;
            flex-shrink: 0;
        }

        .search-category:hover,
        .search-category:focus {
            background-color: #ddd5c0;
        }

        .search-input {
            flex: 1;
            border: none;
            padding: 0 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14.5px;
            color: #111;
            background: white;
            outline: none;
            min-width: 0;
        }

        .search-input::placeholder {
            color: #999;
            font-size: 13.5px;
        }

        .search-btn {
            background: var(--gold);
            border: none;
            padding: 0 20px;
            cursor: pointer;
            color: var(--navy-deep);
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s, transform .1s;
            flex-shrink: 0;
        }

        .search-btn:hover {
            background: #e68900;
        }

        .search-btn:active {
            transform: scale(.96);
        }

        /* ── Desktop right actions ───────────── */
        .desktop-actions {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            padding: 5px 10px;
            border-radius: 6px;
            border: 2px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: border-color .15s;
            color: white;
            position: relative;
        }

        .action-btn:hover {
            border-color: rgba(255, 255, 255, .3);
        }

        .action-btn .label-top {
            font-size: 11px;
            color: rgba(255, 255, 255, .7);
            font-weight: 400;
            white-space: nowrap;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .action-btn .label-bot {
            font-size: 13px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            white-space: nowrap;
        }

        .signup-btn {
            display: flex;
            align-items: center;
            gap: 7px;
            background: var(--gold);
            color: var(--navy-deep);
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 13.5px;
            padding: 9px 14px;
            border-radius: 7px;
            text-decoration: none;
            white-space: nowrap;
            transition: background .15s;
        }

        .signup-btn:hover {
            background: #e68900;
        }

        /* ── Cart badge ──────────────────────── */
        .cart-badge {
            position: absolute;
            top: -2px;
            right: 4px;
            background: var(--gold);
            color: var(--navy-deep);
            font-size: 10px;
            font-weight: 800;
            border-radius: 10px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            animation: pulse-gold 2.5s ease-in-out infinite;
        }

        .cart-badge.hidden-badge {
            display: none;
        }

        @keyframes pulse-gold {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(255, 153, 0, .5)
            }

            50% {
                box-shadow: 0 0 0 5px rgba(255, 153, 0, 0)
            }
        }

        /* ── Hamburger ───────────────────────── */
        .hamburger {
            display: none;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 6px;
            border: 2px solid transparent;
            background: none;
            color: white;
            cursor: pointer;
            font-size: 20px;
            transition: border-color .15s;
            flex-shrink: 0;
        }

        .hamburger:hover {
            border-color: var(--gold);
        }

        /* ── Mobile search bar (below header on mobile) ── */
        .mobile-search-bar {
            display: none;
            background: var(--navy-deep);
            padding: 8px 12px 10px;
            border-top: 1px solid rgba(255, 255, 255, .07);
        }

        .mobile-search-bar .search-wrap {
            max-width: 100%;
            height: 40px;
        }

        .mobile-search-bar .search-category {
            width: 110px;
            min-width: 90px;
            font-size: 11.5px;
        }

        /* ── Mobile nav drawer ───────────────── */
        .mobile-nav {
            display: none;
            flex-direction: column;
            background: var(--navy-deep);
            padding: 10px 14px 14px;
            gap: 2px;
            border-top: 1px solid rgba(255, 255, 255, .06);
        }

        .mobile-nav.open {
            display: flex;
        }

        .mobile-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 12px;
            border-radius: 8px;
            color: rgba(255, 255, 255, .85);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background .15s, color .15s;
        }

        .mobile-nav a:hover {
            background: rgba(255, 153, 0, .1);
            color: var(--gold);
        }

        .mobile-nav a i {
            width: 18px;
            text-align: center;
            color: var(--gold);
            opacity: .85;
        }

        .mobile-nav-divider {
            height: 1px;
            background: rgba(255, 255, 255, .08);
            margin: 6px 0;
        }

        .mobile-cart-count {
            margin-left: auto;
            background: var(--gold);
            color: var(--navy-deep);
            font-size: 11px;
            font-weight: 800;
            border-radius: 10px;
            padding: 1px 7px;
        }

        /* ── Deliver-to button ───────────────── */
        .deliver-to {
            display: none;
            /* shown via JS on wide screens */
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            padding: 5px 8px;
            border-radius: 6px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: border-color .15s;
            color: white;
            white-space: nowrap;
            background: none;
            flex-shrink: 0;
        }

        .deliver-to .dt-top {
            font-size: 11px;
            color: rgba(255, 255, 255, .65);
        }

        .deliver-to .dt-bot {
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
            max-width: 130px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ══════════════════════════════════════
           LOCATION MODAL
        ══════════════════════════════════════ */
        .loc-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .55);
            z-index: 9999;
            align-items: flex-start;
            justify-content: flex-start;
            padding-top: 68px;
            padding-left: 20px;
        }

        .loc-overlay.open {
            display: flex;
        }

        .loc-modal {
            background: white;
            border-radius: 14px;
            width: 440px;
            max-width: calc(100vw - 32px);
            box-shadow: 0 24px 70px rgba(0, 0, 0, .35);
            animation: modalPop .18s ease-out;
        }

        @keyframes modalPop {
            from {
                transform: translateY(-10px) scale(.97);
                opacity: 0
            }

            to {
                transform: translateY(0) scale(1);
                opacity: 1
            }
        }

        .loc-modal-header {
            background: var(--navy-mid);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 14px 14px 0 0;
        }

        .loc-modal-header h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 700;
            color: white;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .loc-modal-header h3 i {
            color: var(--gold);
        }

        .loc-close {
            background: none;
            border: none;
            cursor: pointer;
            color: rgba(255, 255, 255, .6);
            font-size: 18px;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s, color .15s;
        }

        .loc-close:hover {
            background: rgba(255, 255, 255, .1);
            color: white;
        }

        .loc-modal-body {
            padding: 20px;
        }

        .loc-addr-label {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 6px;
        }

        .loc-input-outer {
            position: relative;
            margin-bottom: 14px;
        }

        .loc-input {
            width: 100%;
            border: 2px solid #e2e8f0;
            border-radius: 9px;
            padding: 11px 38px 11px 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: #111;
            outline: none;
            transition: border-color .15s;
        }

        .loc-input:focus {
            border-color: var(--gold);
        }

        .loc-input::placeholder {
            color: #aaa;
        }

        .loc-input-clear {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            font-size: 14px;
            padding: 4px;
            display: none;
        }

        .loc-input-clear.visible {
            display: block;
        }

        .loc-suggestions {
            position: absolute;
            top: calc(100% + 5px);
            left: 0;
            right: 0;
            background: white;
            border: 2px solid var(--gold);
            border-radius: 10px;
            box-shadow: 0 16px 48px rgba(0, 0, 0, .18);
            z-index: 99999;
            max-height: 260px;
            overflow-y: auto;
            display: none;
        }

        .loc-suggestions.visible {
            display: block;
        }

        .loc-sugg-item {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            padding: 11px 14px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            transition: background .1s;
        }

        .loc-sugg-item:last-child {
            border-bottom: none;
        }

        .loc-sugg-item:hover,
        .loc-sugg-item.focused {
            background: #fffbf2;
        }

        .loc-sugg-icon {
            color: var(--gold);
            font-size: 13px;
            margin-top: 3px;
            flex-shrink: 0;
        }

        .loc-sugg-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .loc-sugg-main {
            font-size: 13.5px;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .loc-sugg-sub {
            font-size: 12px;
            color: #64748b;
            margin-top: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .loc-sugg-badge {
            margin-left: auto;
            flex-shrink: 0;
            font-size: 10px;
            font-weight: 700;
            background: #fff3cd;
            color: #92400e;
            border-radius: 4px;
            padding: 2px 6px;
            align-self: center;
        }

        .loc-sugg-msg {
            padding: 14px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .spin {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid #e2e8f0;
            border-top-color: var(--gold);
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }

        .loc-detect-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 11px;
            border: 2px solid #e2e8f0;
            border-radius: 9px;
            background: white;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #3b4a6b;
            cursor: pointer;
            transition: border-color .15s, background .15s;
            margin-bottom: 16px;
        }

        .loc-detect-btn:hover {
            border-color: var(--gold);
            background: #fffbf2;
        }

        .loc-detect-btn i {
            color: var(--gold);
        }

        .loc-divider {
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            margin: 14px 0;
            position: relative;
        }

        .loc-divider::before,
        .loc-divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 42%;
            height: 1px;
            background: #e2e8f0;
        }

        .loc-divider::before {
            left: 0;
        }

        .loc-divider::after {
            right: 0;
        }

        .loc-selected-card {
            display: none;
            align-items: flex-start;
            gap: 12px;
            background: #f0fdf4;
            border: 2px solid #86efac;
            border-radius: 10px;
            padding: 14px;
            margin-top: 14px;
        }

        .loc-selected-card.visible {
            display: flex;
        }

        .sc-icon {
            color: #16a34a;
            font-size: 20px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .sc-body {
            flex: 1;
            min-width: 0;
        }

        .sc-name {
            font-size: 14px;
            font-weight: 700;
            color: #15803d;
        }

        .sc-addr {
            font-size: 12px;
            color: #166534;
            margin-top: 3px;
            line-height: 1.5;
        }

        .sc-change {
            font-size: 12px;
            color: #16a34a;
            cursor: pointer;
            text-decoration: underline;
            margin-top: 4px;
            display: inline-block;
        }

        .loc-confirm-btn {
            background: #16a34a;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 9px 16px;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            align-self: center;
            flex-shrink: 0;
            transition: background .15s;
        }

        .loc-confirm-btn:hover {
            background: #15803d;
        }

        .loc-status {
            margin-top: 10px;
            padding: 9px 13px;
            border-radius: 8px;
            font-size: 13px;
            display: none;
        }

        .loc-status.success {
            background: #f0fdf4;
            color: #16a34a;
            display: block;
        }

        .loc-status.error {
            background: #fef2f2;
            color: #dc2626;
            display: block;
        }

        .loc-status.loading {
            background: #f8fafc;
            color: #64748b;
            display: block;
        }

        /* ══════════════════════════════════════
           RESPONSIVE BREAKPOINTS
        ══════════════════════════════════════ */

        /* Large desktop: show deliver-to, full search */
        @media (min-width: 1024px) {
            .deliver-to {
                display: flex;
            }

            .hamburger {
                display: none !important;
            }
        }

        /* Medium: tablet landscape — hide deliver-to, keep desktop search & actions */
        @media (min-width: 700px) and (max-width: 1023px) {
            .deliver-to {
                display: none !important;
            }

            .hamburger {
                display: none !important;
            }

            .search-category {
                width: 110px;
                min-width: 90px;
            }

            .action-btn .label-top {
                display: none;
            }

            .action-btn .label-bot {
                font-size: 12px;
            }

            .signup-btn span {
                display: none;
            }
        }

        /* Small: mobile — hide desktop search & actions, show hamburger */
        @media (max-width: 699px) {
            .desktop-search {
                display: none !important;
            }

            .desktop-actions {
                display: none !important;
            }

            .deliver-to {
                display: none !important;
            }

            .hamburger {
                display: flex !important;
            }

            .mobile-search-bar {
                display: block;
            }

            .header-inner {
                padding: 8px 14px;
                gap: 8px;
                justify-content: space-between;
            }

            .logo-text {
                font-size: 17px;
            }

            .logo-sub {
                display: none;
            }
        }

        /* Very small screens */
        @media (max-width: 380px) {
            .logo-text {
                font-size: 15px;
            }

            .logo-icon {
                width: 30px;
                height: 30px;
                font-size: 13px;
            }
        }
    </style>
</head>

<body class="flex min-h-screen flex-col">

    <!-- ══════════════════ TOP UTILITY BAR ══════════════════ -->
    <div class="top-bar hidden md:block">
        <div class="top-bar-inner">
            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                <span><i class="fa-solid fa-truck-fast"
                        style="color:var(--gold);margin-right:5px;"></i><?= htmlspecialchars($_['free_shipping']) ?></span>
                <span class="hidden lg:inline">|</span>
                <span class="hidden lg:inline"><i class="fa-solid fa-shield-halved"
                        style="color:var(--gold);margin-right:5px;"></i><?= htmlspecialchars($_['secure']) ?></span>
            </div>
            <div style="display:flex;align-items:center;gap:14px;">
                <a href="#"><i class="fa-solid fa-headset"
                        style="margin-right:4px;"></i><?= htmlspecialchars($_['support']) ?></a>

                <!-- Language Switcher -->
                <div class="lang-switcher" id="langSwitcher">
                    <?php
                    $flags = ['en' => '🇬🇧', 'ta' => '🇱🇰', 'si' => '🇱🇰'];
                    $labels = ['en' => 'EN', 'ta' => 'தமிழ்', 'si' => 'සිං'];
                    $names = ['en' => 'English', 'ta' => 'Tamil', 'si' => 'Sinhala'];
                    $natives = ['en' => 'English', 'ta' => 'தமிழ்', 'si' => 'සිංහල'];
                    ?>
                    <button class="lang-btn" onclick="toggleLangDropdown(event)" type="button">
                        <span><?= $flags[$lang] ?></span>
                        <span><?= $labels[$lang] ?></span>
                        <i class="fa-solid fa-chevron-down" style="font-size:8px;opacity:.7;"></i>
                    </button>
                    <div class="lang-dropdown" id="langDropdown">
                        <?php foreach (['en', 'ta', 'si'] as $code): ?>
                            <a class="lang-option <?= $lang === $code ? 'active' : '' ?>"
                                href="?<?= http_build_query(array_merge($_GET, ['lang' => $code])) ?>">
                                <span class="lf"><?= $flags[$code] ?></span>
                                <span class="lname"><?= $names[$code] ?></span>
                                <span class="lnative"><?= $natives[$code] ?></span>
                                <?php if ($lang === $code): ?>
                                    <i class="fa-solid fa-check" style="color:var(--gold);font-size:11px;"></i>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════ MAIN HEADER ══════════════════ -->
    <header class="main-header">
        <div class="header-inner">

            <!-- LOGO -->
            <a href="index.php" class="logo-wrap">
                <div class="logo-icon"><i class="fa-solid fa-store"></i></div>
                <div class="logo-text">
                    <span>Retail<span>Hub</span></span>
                    <span class="logo-sub"><?= htmlspecialchars($_['premium_store']) ?></span>
                </div>
            </a>

            <!-- DELIVER TO (desktop ≥1024px) -->
            <button type="button" class="deliver-to" id="deliverToBtn" onclick="openLocModal()">
                <span class="dt-top"><?= htmlspecialchars($_['deliver_to']) ?></span>
                <span class="dt-bot">
                    <i class="fa-solid fa-location-dot" style="color:var(--gold);font-size:12px;flex-shrink:0;"></i>
                    <span id="deliverLabelText"
                        style="overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($_['select_location']) ?></span>
                </span>
            </button>

            <!-- SEARCH BAR — desktop (hidden on mobile) -->
            <div class="search-wrap desktop-search" style="flex:1;">
                <select class="search-category" name="category" id="searchCategory" aria-label="Search category">
                    <option value=""><?= htmlspecialchars($_['all_categories']) ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>" <?= ($currentCat == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input class="search-input" type="text" id="searchInput" name="q"
                    value="<?= htmlspecialchars($currentQ) ?>" placeholder="<?= htmlspecialchars($_['search_ph']) ?>"
                    autocomplete="off" aria-label="Search">
                <button class="search-btn" type="button" onclick="doSearch()" aria-label="Search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>

            <!-- RIGHT ACTIONS — desktop & tablet -->
            <div class="desktop-actions">
                <?php if (isLoggedIn()): ?>
                    <?php
                    $accountLink = "index.php?page=dashboard";

                    if (isAdmin()) {
                        $accountLink = "index.php?page=admin";
                    } elseif (isSupportStaff()) {
                        $accountLink = "index.php?page=admin-helpdesk";
                    }
                    ?>

                    <a href="<?= $accountLink ?>" class="action-btn">
                        <span class="label-top">
                            <?= htmlspecialchars($_['hello']) ?>     <?= htmlspecialchars(currentUser()['full_name'] ?? '') ?>
                        </span>
                        <span class="label-bot">
                            <i class="fa-solid fa-user" style="font-size:11px;margin-right:3px;"></i>
                            <?= htmlspecialchars($_['my_account']) ?>
                        </span>
                    </a>
                    <a href="index.php?page=orders" class="action-btn">
                        <span class="label-top"><?= htmlspecialchars($_['returns']) ?></span>
                        <span class="label-bot"><?= htmlspecialchars($_['orders']) ?></span>
                    </a>
                    <a href="index.php?page=cart" class="action-btn" style="padding-right:18px;">
                        <span class="cart-badge <?= $cartCount === 0 ? 'hidden-badge' : '' ?>" id="cartBadge">
                            <?= $cartCount > 99 ? '99+' : $cartCount ?>
                        </span>
                        <i class="fa-solid fa-cart-shopping" style="font-size:26px;color:white;"></i>
                        <span class="label-bot"
                            style="font-size:11px;font-weight:600;"><?= htmlspecialchars($_['cart']) ?></span>
                    </a>
                    <a href="index.php?page=logout" class="action-btn" style="padding:5px 8px;">
                        <i class="fa-solid fa-right-from-bracket" style="font-size:17px;color:rgba(255,255,255,.7);"></i>
                        <span
                            style="font-size:11px;color:rgba(255,255,255,.7);"><?= htmlspecialchars($_['logout']) ?></span>
                    </a>
                <?php else: ?>
                    <a href="index.php?page=login" class="action-btn">
                        <span class="label-top"><?= htmlspecialchars($_['sign_in']) ?></span>
                        <span class="label-bot"><i class="fa-solid fa-user"
                                style="font-size:11px;margin-right:3px;"></i><?= htmlspecialchars($_['account']) ?> <i
                                class="fa-solid fa-chevron-down" style="font-size:9px;"></i></span>
                    </a>
                    <a href="index.php?page=cart" class="action-btn" style="padding-right:18px;">
                        <span class="cart-badge hidden-badge" id="cartBadge">0</span>
                        <i class="fa-solid fa-cart-shopping" style="font-size:26px;color:white;"></i>
                        <span class="label-bot"
                            style="font-size:11px;font-weight:600;"><?= htmlspecialchars($_['cart']) ?></span>
                    </a>
                    <a href="index.php?page=register" class="signup-btn">
                        <i class="fa-solid fa-user-plus" style="font-size:12px;"></i>
                        <span><?= htmlspecialchars($_['sign_up']) ?></span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- HAMBURGER — mobile only (≤699px) -->
            <button class="hamburger" id="menuToggle" aria-label="Open menu" aria-expanded="false">
                <i class="fa-solid fa-bars" id="hamburgerIcon"></i>
            </button>

        </div>

        <!-- MOBILE SEARCH BAR (shown below header row on small screens) -->
        <div class="mobile-search-bar" id="mobileSearchBar">
            <div class="search-wrap" style="max-width:100%;">
                <select class="search-category" id="searchCategoryMobile" aria-label="Search category">
                    <option value=""><?= htmlspecialchars($_['all_categories']) ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>" <?= ($currentCat == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input class="search-input" type="text" id="searchInputMobile"
                    value="<?= htmlspecialchars($currentQ) ?>" placeholder="<?= htmlspecialchars($_['search_ph']) ?>"
                    aria-label="Search">
                <button class="search-btn" type="button" onclick="doSearchMobile()" aria-label="Search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
        </div>

        <!-- MOBILE NAV DRAWER -->
        <nav class="mobile-nav" id="mobileNav" aria-label="Mobile navigation">
            <a href="index.php"><i class="fa-solid fa-house"></i><?= htmlspecialchars($_['home']) ?></a>
            <a href="index.php?page=products"><i
                    class="fa-solid fa-bag-shopping"></i><?= htmlspecialchars($_['shop_all']) ?></a>
            <a href="index.php?page=cart">
                <i class="fa-solid fa-cart-shopping"></i><?= htmlspecialchars($_['cart']) ?>
                <?php if ($cartCount > 0): ?>
                    <span class="mobile-cart-count"><?= $cartCount > 99 ? '99+' : $cartCount ?></span>
                <?php endif; ?>
            </a>
            <?php if (isLoggedIn()): ?>
                <div class="mobile-nav-divider"></div>
                <a href="index.php?page=dashboard"><i
                        class="fa-solid fa-grip"></i><?= htmlspecialchars($_['dashboard']) ?></a>
                <a href="index.php?page=orders"><i class="fa-solid fa-box"></i><?= htmlspecialchars($_['my_orders']) ?></a>
                <a href="index.php?page=support"><i
                        class="fa-solid fa-headset"></i><?= htmlspecialchars($_['support']) ?></a>
                <?php if (isAdmin()): ?>
                    <a href="index.php?page=admin" style="color:var(--gold);"><i
                            class="fa-solid fa-gauge-high"></i><?= htmlspecialchars($_['admin']) ?></a>
                <?php elseif (isSupportStaff()): ?>
                    <a href="index.php?page=admin-helpdesk" style="color:var(--gold);"><i
                            class="fa-solid fa-headset"></i><?= htmlspecialchars($_['support_desk']) ?></a>
                <?php endif; ?>
                <div class="mobile-nav-divider"></div>
                <a href="index.php?page=logout" style="color:rgba(255,100,100,.85);"><i
                        class="fa-solid fa-right-from-bracket"
                        style="color:rgba(255,100,100,.7);"></i><?= htmlspecialchars($_['logout']) ?></a>
            <?php else: ?>
                <div class="mobile-nav-divider"></div>
                <a href="index.php?page=login"><i class="fa-solid fa-right-to-bracket"></i>Login</a>
                <a href="index.php?page=register" style="color:var(--gold);font-weight:700;"><i
                        class="fa-solid fa-user-plus"></i><?= htmlspecialchars($_['create_account']) ?></a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- ════════════════ LOCATION MODAL ════════════════ -->
    <div class="loc-overlay" id="locOverlay" onclick="handleOverlayClick(event)">
        <div class="loc-modal" id="locModal">
            <div class="loc-modal-header">
                <h3><i class="fa-solid fa-location-dot"></i>Choose delivery location</h3>
                <button class="loc-close" onclick="closeLocModal()" aria-label="Close"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="loc-modal-body">
                <div class="loc-addr-label"><i class="fa-solid fa-magnifying-glass"
                        style="margin-right:5px;color:var(--gold);"></i>Search your address in Sri Lanka</div>
                <div class="loc-input-outer">
                    <input class="loc-input" type="text" id="addrInput"
                        placeholder="e.g. Colombo 3, Kandy, Galle Fort..." autocomplete="off"
                        aria-label="Address search" oninput="onAddrInput(this.value)" onkeydown="onAddrKeydown(event)">
                    <button class="loc-input-clear" id="addrClearBtn" onclick="clearAddrInput()" title="Clear">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <div class="loc-suggestions" id="locSuggestions" role="listbox"></div>
                </div>
                <button class="loc-detect-btn" id="detectBtn" onclick="detectLocation()">
                    <i class="fa-solid fa-crosshairs"></i><span id="detectBtnText">Use my current location</span>
                </button>
                <div class="loc-divider">or select a city</div>
                <div style="display:flex;flex-wrap:wrap;gap:7px;margin-bottom:4px;">
                    <?php
                    $sriLankaCities = [
                        ['Colombo', 'Colombo District'],
                        ['Kandy', 'Central Province'],
                        ['Galle', 'Southern Province'],
                        ['Negombo', 'Western Province'],
                        ['Jaffna', 'Northern Province'],
                        ['Matara', 'Southern Province'],
                        ['Batticaloa', 'Eastern Province'],
                        ['Trincomalee', 'Eastern Province'],
                        ['Anuradhapura', 'North Central'],
                        ['Kurunegala', 'North Western'],
                        ['Ratnapura', 'Sabaragamuwa'],
                        ['Badulla', 'Uva Province'],
                    ];
                    foreach ($sriLankaCities as $city): ?>
                        <button onclick="selectCity('<?= $city[0] ?>','<?= $city[1] ?>')"
                            style="display:flex;align-items:center;gap:5px;padding:6px 12px;border:1.5px solid #e2e8f0;border-radius:20px;background:white;font-family:'DM Sans',sans-serif;font-size:12.5px;font-weight:600;color:#374151;cursor:pointer;transition:all .15s;"
                            onmouseover="this.style.borderColor='var(--gold)';this.style.background='#fffbf2';this.style.color='#b45309';"
                            onmouseout="this.style.borderColor='#e2e8f0';this.style.background='white';this.style.color='#374151';">
                            <i class="fa-solid fa-map-pin" style="color:var(--gold);font-size:10px;"></i><?= $city[0] ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="loc-selected-card" id="selectedCard">
                    <i class="fa-solid fa-circle-check sc-icon"></i>
                    <div class="sc-body">
                        <div class="sc-name" id="selectedName">—</div>
                        <div class="sc-addr" id="selectedAddr">—</div>
                        <span class="sc-change" onclick="changeAddress()">Change address</span>
                    </div>
                    <button class="loc-confirm-btn" onclick="confirmDelivery()">
                        <i class="fa-solid fa-check" style="margin-right:5px;"></i>Deliver here
                    </button>
                </div>
                <div class="loc-status" id="locStatus"></div>
            </div>
        </div>
    </div>

    <script>
        // ── Mobile nav toggle ─────────────────────────────────
        const mobileNav = document.getElementById('mobileNav');
        const hamburgerBtn = document.getElementById('menuToggle');
        const hamburgerIco = document.getElementById('hamburgerIcon');

        hamburgerBtn.addEventListener('click', () => {
            const open = mobileNav.classList.toggle('open');
            hamburgerBtn.setAttribute('aria-expanded', open);
            hamburgerIco.className = open ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
        });

        // Close nav when clicking a link inside it
        mobileNav.querySelectorAll('a').forEach(a =>
            a.addEventListener('click', () => {
                mobileNav.classList.remove('open');
                hamburgerBtn.setAttribute('aria-expanded', 'false');
                hamburgerIco.className = 'fa-solid fa-bars';
            })
        );

        // ── Search ────────────────────────────────────────────
        function doSearch() {
            buildSearchURL(
                document.getElementById('searchInput').value.trim(),
                document.getElementById('searchCategory').value
            );
        }
        function doSearchMobile() {
            buildSearchURL(
                document.getElementById('searchInputMobile').value.trim(),
                document.getElementById('searchCategoryMobile').value
            );
        }
        function buildSearchURL(q, cat) {
            let url = 'index.php?page=products';
            if (cat) url += '&category=' + encodeURIComponent(cat);
            if (q) url += '&q=' + encodeURIComponent(q);
            window.location.href = url;
        }

        document.getElementById('searchInput')?.addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });
        document.getElementById('searchInputMobile')?.addEventListener('keydown', e => { if (e.key === 'Enter') doSearchMobile(); });
        document.getElementById('searchCategory')?.addEventListener('change', function () {
            buildSearchURL(document.getElementById('searchInput').value.trim(), this.value);
        });
        document.getElementById('searchCategoryMobile')?.addEventListener('change', function () {
            buildSearchURL(document.getElementById('searchInputMobile').value.trim(), this.value);
        });

        // ── Deliver-to label from localStorage ───────────────
        const savedLoc = localStorage.getItem('retailhub_delivery');
        if (savedLoc) {
            try {
                const d = JSON.parse(savedLoc);
                document.getElementById('deliverLabelText').textContent = d.short || d.name;
            } catch (e) { }
        }

        // ── Language dropdown ─────────────────────────────────
        function toggleLangDropdown(e) {
            e.stopPropagation();
            document.getElementById('langDropdown').classList.toggle('open');
        }
        document.addEventListener('click', e => {
            if (!e.target.closest('#langSwitcher'))
                document.getElementById('langDropdown')?.classList.remove('open');
        });

        // ── Cart badge refresh ────────────────────────────────
        function refreshCartBadge() {
            fetch('index.php?action=cart_count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json()).then(data => {
                    const b = document.getElementById('cartBadge');
                    if (!b) return;
                    const n = data.count || 0;
                    b.textContent = n > 99 ? '99+' : n;
                    b.classList.toggle('hidden-badge', n === 0);
                }).catch(() => { });
        }
        setInterval(refreshCartBadge, 60000);

        // ── Location modal ────────────────────────────────────
        function openLocModal() {
            document.getElementById('locOverlay').classList.add('open');
            clearStatus();
            setTimeout(() => document.getElementById('addrInput').focus(), 120);
            const saved = localStorage.getItem('retailhub_delivery');
            if (saved) {
                try { const d = JSON.parse(saved); showSelectedCard(d.name, d.address || d.name); } catch (e) { }
            }
        }
        function closeLocModal() {
            document.getElementById('locOverlay').classList.remove('open');
            hideSuggestions();
        }
        function handleOverlayClick(e) {
            if (e.target === document.getElementById('locOverlay')) closeLocModal();
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLocModal(); });
        function setDeliverLabel(t) { document.getElementById('deliverLabelText').textContent = t; }
        function showStatus(msg, type) { const el = document.getElementById('locStatus'); el.textContent = msg; el.className = 'loc-status ' + type; }
        function clearStatus() { const el = document.getElementById('locStatus'); el.textContent = ''; el.className = 'loc-status'; }

        // ── Address autocomplete ──────────────────────────────
        let searchTimeout = null, focusIdx = -1, lastResults = [];

        function onAddrInput(val) {
            document.getElementById('addrClearBtn').classList.toggle('visible', val.length > 0);
            document.getElementById('selectedCard').classList.remove('visible');
            clearStatus();
            if (val.length < 3) { hideSuggestions(); return; }
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => fetchAddresses(val), 380);
        }
        function clearAddrInput() {
            document.getElementById('addrInput').value = '';
            document.getElementById('addrClearBtn').classList.remove('visible');
            document.getElementById('selectedCard').classList.remove('visible');
            hideSuggestions(); clearStatus();
            document.getElementById('addrInput').focus();
        }
        async function fetchAddresses(query) {
            showSuggestionsLoading();
            const url = 'https://nominatim.openstreetmap.org/search?' + new URLSearchParams({
                q: query + ', Sri Lanka', format: 'json',
                addressdetails: 1, limit: 8, countrycodes: 'lk', 'accept-language': 'en'
            });
            try {
                const data = await (await fetch(url, { headers: { 'Accept-Language': 'en' } })).json();
                if (!data?.length) { showSuggestionsEmpty('No results found. Try a different area.'); return; }
                lastResults = data; renderSuggestions(data);
            } catch { showSuggestionsEmpty('Unable to fetch. Check your connection.'); }
        }
        function renderSuggestions(results) {
            const box = document.getElementById('locSuggestions'); focusIdx = -1; box.innerHTML = '';
            results.forEach((r, i) => {
                const a = r.address || {}, parts = r.display_name.split(', ');
                const main = parts[0], sub = parts.slice(1).join(', ');
                const badge = a.city || a.town || a.village || a.county || '';
                const el = document.createElement('div');
                el.className = 'loc-sugg-item'; el.role = 'option';
                el.innerHTML = `<i class="fa-solid fa-location-dot loc-sugg-icon"></i>
                <div class="loc-sugg-info">
                    <div class="loc-sugg-main">${escHtml(main)}</div>
                    <div class="loc-sugg-sub">${escHtml(sub)}</div>
                </div>
                ${badge ? `<span class="loc-sugg-badge">${escHtml(badge)}</span>` : ''}`;
                el.addEventListener('click', () => selectAddress(r));
                el.addEventListener('mouseenter', () => setFocus(i));
                box.appendChild(el);
            });
            box.classList.add('visible');
        }
        function showSuggestionsLoading() {
            const b = document.getElementById('locSuggestions');
            b.innerHTML = `<div class="loc-sugg-msg"><span class="spin"></span> Searching Sri Lankan addresses…</div>`;
            b.classList.add('visible');
        }
        function showSuggestionsEmpty(msg) {
            const b = document.getElementById('locSuggestions');
            b.innerHTML = `<div class="loc-sugg-msg"><i class="fa-solid fa-circle-exclamation" style="color:#f59e0b;"></i> ${escHtml(msg)}</div>`;
            b.classList.add('visible');
        }
        function hideSuggestions() {
            const b = document.getElementById('locSuggestions');
            b.classList.remove('visible'); b.innerHTML = ''; focusIdx = -1;
        }
        function setFocus(idx) {
            document.querySelectorAll('.loc-sugg-item').forEach(i => i.classList.remove('focused'));
            document.querySelectorAll('.loc-sugg-item')[idx]?.classList.add('focused');
            focusIdx = idx;
        }
        function onAddrKeydown(e) {
            const items = document.querySelectorAll('.loc-sugg-item');
            if (e.key === 'ArrowDown') { e.preventDefault(); setFocus(Math.min(focusIdx + 1, items.length - 1)); }
            if (e.key === 'ArrowUp') { e.preventDefault(); setFocus(Math.max(focusIdx - 1, 0)); }
            if (e.key === 'Enter' && focusIdx >= 0 && lastResults[focusIdx]) { e.preventDefault(); selectAddress(lastResults[focusIdx]); }
            if (e.key === 'Escape') hideSuggestions();
        }
        function selectAddress(r) {
            hideSuggestions();
            const a = r.address || {}, full = r.display_name;
            const short = [a.road || a.pedestrian || a.neighbourhood, a.suburb || a.village || a.town || a.city || a.county]
                .filter(Boolean).join(', ') || full.split(', ').slice(0, 2).join(', ');
            document.getElementById('addrInput').value = short;
            document.getElementById('addrClearBtn').classList.add('visible');
            showSelectedCard(short, full); clearStatus();
        }
        function showSelectedCard(name, addr) {
            document.getElementById('selectedName').textContent = name;
            document.getElementById('selectedAddr').textContent = addr;
            document.getElementById('selectedCard').classList.add('visible');
        }
        function changeAddress() {
            document.getElementById('selectedCard').classList.remove('visible');
            document.getElementById('addrInput').value = '';
            document.getElementById('addrClearBtn').classList.remove('visible');
            document.getElementById('addrInput').focus();
        }
        function confirmDelivery() {
            const name = document.getElementById('selectedName').textContent;
            const address = document.getElementById('selectedAddr').textContent;
            if (!name || name === '—') { showStatus('Please select a delivery address first.', 'error'); return; }
            const data = { name, address, short: name.split(',')[0] };
            localStorage.setItem('retailhub_delivery', JSON.stringify(data));
            setDeliverLabel(data.short);
            showStatus('✓ Delivery address set to: ' + name, 'success');
            fetch('index.php?action=set_location', {
                method: 'POST', body: JSON.stringify(data),
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(() => { });
            setTimeout(closeLocModal, 1200);
        }
        function selectCity(city, province) {
            document.getElementById('addrInput').value = city;
            document.getElementById('addrClearBtn').classList.add('visible');
            showSelectedCard(city + ', Sri Lanka', city + ', ' + province + ', Sri Lanka');
            hideSuggestions(); clearStatus();
        }
        function detectLocation() {
            if (!navigator.geolocation) { showStatus('Geolocation not supported.', 'error'); return; }
            const btn = document.getElementById('detectBtn'), txt = document.getElementById('detectBtnText');
            txt.textContent = 'Detecting…'; btn.disabled = true;
            navigator.geolocation.getCurrentPosition(
                async pos => {
                    const { latitude: lat, longitude: lng } = pos.coords;
                    try {
                        const data = await (await fetch(
                            `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&addressdetails=1`
                        )).json();
                        const a = data.address || {};
                        const short = [a.road || a.neighbourhood, a.city || a.town || a.village].filter(Boolean).join(', ')
                            || 'Near your location';
                        document.getElementById('addrInput').value = short;
                        document.getElementById('addrClearBtn').classList.add('visible');
                        showSelectedCard(short, data.display_name || short);
                    } catch {
                        showSelectedCard('Your location', `${lat.toFixed(4)}, ${lng.toFixed(4)}`);
                    }
                    txt.textContent = 'Use my current location'; btn.disabled = false;
                },
                err => {
                    showStatus({ 1: 'Permission denied.', 2: 'Unavailable.', 3: 'Timed out.' }[err.code] || 'Error.', 'error');
                    txt.textContent = 'Use my current location'; btn.disabled = false;
                },
                { timeout: 10000 }
            );
        }
        function escHtml(s) {
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
        document.addEventListener('click', e => {
            if (!e.target.closest('.loc-input-outer')) hideSuggestions();
        });
    </script>
</body>

</html>