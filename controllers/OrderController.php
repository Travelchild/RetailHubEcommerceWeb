<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Cart.php';

class OrderController
{
    private Order $orderModel;
    private Product $productModel;
    private Cart $cartModel;

    /** @var array<string, array{label:string, blurb:string, icon:string}> */
    private const PAYMENT_GATEWAYS = [
        'cod' => [
            'label' => 'Cash on delivery',
            'blurb' => 'Pay when your order arrives',
            'icon' => 'fa-solid fa-money-bill-wave',
        ],
        'card' => [
            'label' => 'Visa / Mastercard',
            'blurb' => 'Pay with your Visa or Mastercard',
            'icon' => 'fa-solid fa-credit-card',
        ],
    ];

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->productModel = new Product();
        $this->cartModel = new Cart();
    }

    public function history(): array
    {
        $orders = $this->orderModel->byUser((int)$_SESSION['user']['id']);
        return ['view' => 'orders/history', 'data' => ['orders' => $orders]];
    }

    public function invoicePdf(): void
    {
        $orderId = (int)($_GET['id'] ?? 0);
        if ($orderId < 1) {
            http_response_code(400);
            echo 'Invalid invoice.';
            exit;
        }

        $userId = (int)$_SESSION['user']['id'];
        $order = $this->orderModel->findForUser($orderId, $userId);
        if (!$order) {
            http_response_code(404);
            echo 'Invoice not found.';
            exit;
        }

        $items = $this->orderModel->itemsForOrder($orderId);
        require_once __DIR__ . '/../includes/InvoicePdf.php';
        InvoicePdf::stream($order, $items);
        exit;
    }

    public function checkout(): array
    {
        $error = null;
        $success = null;
        $lastOrderId = null;
        $total = 0.0;
        $lineItems = [];

        $userId = (int)$_SESSION['user']['id'];
        $cartLines = $this->cartModel->linesForUser($userId);

        foreach ($cartLines as $productId => $qty) {
            $product = $this->productModel->find((int)$productId);
            if ($product) {
                $qty = (int)$qty;
                $sub = $qty * (float)$product['price'];
                $total += $sub;
                $lineItems[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'subtotal' => $sub,
                ];
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($total <= 0) {
                $error = 'Cart is empty.';
            } else {
                $gateway = $_POST['payment_gateway'] ?? 'cod';
                if (!isset(self::PAYMENT_GATEWAYS[$gateway])) {
                    $gateway = 'cod';
                }

                $address = trim((string)($_POST['shipping_address'] ?? ''));
                if ($address === '') {
                    $error = 'Shipping address is required.';
                } elseif (($cardErr = $this->validateCardDetails($gateway, $_POST)) !== null) {
                    $error = $cardErr;
                } else {
                    $paymentOutcome = $this->finalizeDemoPayment($gateway);
                    if (!$paymentOutcome['ok']) {
                        $error = $paymentOutcome['message'] ?? 'Payment could not be completed.';
                    } else {
                        $methodLabel = $this->paymentMethodLabel($gateway, $_POST);
                        $orderId = $this->orderModel->createOrder(
                            $userId,
                            $total,
                            $address,
                            $methodLabel,
                            $gateway,
                            $paymentOutcome['payment_status'],
                            $paymentOutcome['transaction_id'],
                            $paymentOutcome['order_status']
                        );

                        foreach ($cartLines as $productId => $qty) {
                            $product = $this->productModel->find((int)$productId);
                            if ($product) {
                                $this->orderModel->addItem($orderId, (int)$productId, (int)$qty, (float)$product['price']);
                            }
                        }

                        $this->cartModel->clearForUser($userId);
                        $lastOrderId = $orderId;
                        $success = "Order #{$orderId} placed successfully. Your payment is {$paymentOutcome['payment_status']}.";
                    }
                }
            }
        }

        return [
            'view' => 'orders/checkout',
            'data' => [
                'total' => $total,
                'lineItems' => $lineItems,
                'error' => $error,
                'success' => $success,
                'lastOrderId' => $lastOrderId,
                'gateways' => self::PAYMENT_GATEWAYS,
            ],
        ];
    }

    /**
     * @return array{ok: bool, payment_status: string, transaction_id: ?string, order_status: string, message?: string}
     */
    private function finalizeDemoPayment(string $gateway): array
    {
        if ($gateway === 'cod') {
            return [
                'ok' => true,
                'payment_status' => 'Pending (COD)',
                'transaction_id' => null,
                'order_status' => 'Pending',
            ];
        }

        if ($gateway !== 'card') {
            return [
                'ok' => false,
                'payment_status' => 'Failed',
                'transaction_id' => null,
                'order_status' => 'Pending',
                'message' => 'Invalid payment method.',
            ];
        }

        // Demo card (Visa/Master): simulate successful capture
        $txn = 'TXN-' . strtoupper(bin2hex(random_bytes(5)));
        return [
            'ok' => true,
            'payment_status' => 'Paid',
            'transaction_id' => $txn,
            'order_status' => 'Processing',
        ];
    }

    /**
     * @param array<string, string> $post
     */
    private function paymentMethodLabel(string $gateway, array $post): string
    {
        if ($gateway !== 'card') {
            return 'Cash on delivery';
        }
        $digits = preg_replace('/\D/', '', (string)($post['card_number'] ?? ''));
        $last4 = substr($digits, -4);
        $suffix = strlen($last4) === 4 ? ' ·••• ' . $last4 : '';

        return 'Visa / Mastercard' . $suffix;
    }

    /** @param array<string, string> $post */
    private function validateCardDetails(string $gateway, array $post): ?string
    {
        if ($gateway !== 'card') {
            return null;
        }
        $name = trim((string)($post['card_name'] ?? ''));
        $num = preg_replace('/\D/', '', (string)($post['card_number'] ?? ''));
        $exp = preg_replace('/\s/', '', (string)($post['card_expiry'] ?? ''));
        $cvv = preg_replace('/\D/', '', (string)($post['card_cvv'] ?? ''));

        if ($name === '' || strlen($name) < 2) {
            return 'Please enter the name on card.';
        }
        if (strlen($num) < 13 || strlen($num) > 19) {
            return 'Please enter a valid card number.';
        }
        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $exp)) {
            return 'Please enter expiry as MM/YY (e.g. 12/28).';
        }
        if (strlen($cvv) < 3 || strlen($cvv) > 4) {
            return 'Please enter a valid CVV.';
        }

        return null;
    }
}

