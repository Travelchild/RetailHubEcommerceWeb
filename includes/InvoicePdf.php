<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class InvoicePdf
{
    /**
     * @param array<string, mixed> $order Row from orders + joined user fields
     * @param array<int, array<string, mixed>> $items Rows from order_items join products
     */
    public static function stream(array $order, array $items): void
    {
        $html = self::buildHtml($order, $items);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $id = (int)($order['id'] ?? 0);
        $filename = 'RetailHub-Invoice-' . $id . '.pdf';

        $dompdf->stream($filename, ['Attachment' => true]);
    }

    /**
     * @param array<string, mixed> $order
     * @param array<int, array<string, mixed>> $items
     */
    private static function gatewayLabel(string $key): string
    {
        return match ($key) {
            'cod' => 'Cash on delivery',
            'card' => 'Visa / Mastercard',
            // legacy rows from older installs
            'stripe', 'paypal', 'payhere' => 'Card / wallet (legacy demo)',
            default => $key !== '' ? $key : '—',
        };
    }

    private static function buildHtml(array $order, array $items): string
    {
        $id = (int)($order['id'] ?? 0);
        $total = (float)($order['total_amount'] ?? 0);
        $created = htmlspecialchars((string)($order['created_at'] ?? ''));
        $ship = htmlspecialchars((string)($order['shipping_address'] ?? ''));
        $pm = htmlspecialchars((string)($order['payment_method'] ?? ''));
        $gw = htmlspecialchars(self::gatewayLabel((string)($order['payment_gateway'] ?? '')));
        $pStatus = htmlspecialchars((string)($order['payment_status'] ?? ''));
        $txn = isset($order['payment_transaction_id']) && $order['payment_transaction_id'] !== ''
            ? htmlspecialchars((string)$order['payment_transaction_id'])
            : '—';
        $custName = htmlspecialchars((string)($order['full_name'] ?? ''));
        $custEmail = htmlspecialchars((string)($order['email'] ?? ''));
        $isCod = ($order['payment_gateway'] ?? '') === 'cod';

        $rows = '';
        foreach ($items as $row) {
            $name = htmlspecialchars((string)($row['product_name'] ?? ''));
            $qty = (int)($row['quantity'] ?? 0);
            $unit = number_format((float)($row['unit_price'] ?? 0), 2, '.', ',');
            $sub = number_format((float)($row['subtotal'] ?? 0), 2, '.', ',');
            $rows .= '<tr>'
                . '<td style="padding:8px 6px;border-bottom:1px solid #e2e8f0;">' . $name . '</td>'
                . '<td style="padding:8px 6px;border-bottom:1px solid #e2e8f0;text-align:center;">' . $qty . '</td>'
                . '<td style="padding:8px 6px;border-bottom:1px solid #e2e8f0;text-align:right;">Rs. ' . $unit . '</td>'
                . '<td style="padding:8px 6px;border-bottom:1px solid #e2e8f0;text-align:right;">Rs. ' . $sub . '</td>'
                . '</tr>';
        }

        $totalFmt = number_format($total, 2, '.', ',');
        $title = $isCod ? 'Order confirmation' : 'Tax invoice';

        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Invoice #' . $id . '</title></head>'
            . '<body style="font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0f172a;">'
            . '<table style="width:100%;margin-bottom:24px;"><tr>'
            . '<td style="vertical-align:top;"><div style="font-size:20px;font-weight:bold;color:#4f46e5;">RetailHub</div></td>'
            . '<td style="text-align:right;vertical-align:top;">'
            . '<div style="font-size:16px;font-weight:bold;">' . htmlspecialchars($title) . '</div>'
            . '<div style="margin-top:6px;"><strong>Order #</strong> ' . $id . '</div>'
            . '<div><strong>Date</strong> ' . $created . '</div>'
            . '</td></tr></table>'

            . '<table style="width:100%;margin-bottom:20px;"><tr>'
            . '<td style="width:50%;vertical-align:top;padding-right:16px;">'
            . '<div style="font-size:10px;text-transform:uppercase;color:#64748b;font-weight:bold;">Bill to</div>'
            . '<div style="font-weight:bold;margin-top:4px;">' . $custName . '</div>'
            . '<div style="color:#334155;">' . $custEmail . '</div>'
            . '</td><td style="width:50%;vertical-align:top;">'
            . '<div style="font-size:10px;text-transform:uppercase;color:#64748b;font-weight:bold;">Ship to</div>'
            . '<div style="margin-top:4px;white-space:pre-wrap;">' . $ship . '</div>'
            . '</td></tr></table>'

            . '<div style="font-size:10px;text-transform:uppercase;color:#64748b;font-weight:bold;margin-bottom:6px;">Payment</div>'
            . '<table style="width:100%;margin-bottom:20px;font-size:11px;"><tr>'
            . '<td><strong>Method</strong> ' . $pm . '</td>'
            . '<td><strong>Gateway</strong> ' . $gw . '</td>'
            . '<td><strong>Status</strong> ' . $pStatus . '</td>'
            . '<td><strong>Reference</strong> ' . $txn . '</td>'
            . '</tr></table>'

            . '<table style="width:100%;border-collapse:collapse;">'
            . '<thead><tr style="background:#f1f5f9;">'
            . '<th style="text-align:left;padding:8px 6px;font-size:10px;text-transform:uppercase;color:#64748b;">Item</th>'
            . '<th style="text-align:center;padding:8px 6px;font-size:10px;text-transform:uppercase;color:#64748b;">Qty</th>'
            . '<th style="text-align:right;padding:8px 6px;font-size:10px;text-transform:uppercase;color:#64748b;">Unit</th>'
            . '<th style="text-align:right;padding:8px 6px;font-size:10px;text-transform:uppercase;color:#64748b;">Subtotal</th>'
            . '</tr></thead><tbody>' . $rows . '</tbody></table>'

            . '<table style="width:100%;margin-top:16px;"><tr>'
            . '<td></td><td style="width:220px;">'
            . '<div style="display:flex;justify-content:space-between;padding:10px 12px;background:#eef2ff;border-radius:8px;font-weight:bold;">'
            . '<span>Total</span><span>Rs. ' . $totalFmt . '</span>'
            . '</div></td></tr></table>'

            . '<p style="margin-top:28px;font-size:10px;color:#94a3b8;">This document was generated electronically for RetailHub. '
            . ($isCod ? 'Payment is due on delivery.' : 'Thank you for your payment.') . '</p>'
            . '</body></html>';
    }
}
