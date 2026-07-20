<?php

namespace App\Services\Crm;

class FinancialCalculator
{
    /**
     * @param  array<int, array{quantity: int|float, unit_price: float|int|string}>  $items
     * @return array{subtotal: float, tax_amount: float, discount_amount: float, total: float}
     */
    public function calculate(array $items, float $taxPercentage = 0, float $discountPercentage = 0): array
    {
        $subtotal = 0.0;

        foreach ($items as $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $subtotal += round($quantity * $unitPrice, 2);
        }

        $taxAmount = round($subtotal * ($taxPercentage / 100), 2);
        $discountAmount = round($subtotal * ($discountPercentage / 100), 2);
        $total = round($subtotal + $taxAmount - $discountAmount, 2);

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total' => max(0, $total),
        ];
    }

    /**
     * @param  array<int, array{quantity: int|float, unit_price: float|int|string}>  $items
     * @return array<int, array{description: string, quantity: int, unit_price: float, total: float}>
     */
    public function normalizeLineItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $quantity = max(0, (int) ($item['quantity'] ?? 0));
            $unitPrice = round((float) ($item['unit_price'] ?? 0), 2);
            $total = round($quantity * $unitPrice, 2);

            $normalized[] = [
                'description' => (string) ($item['description'] ?? ''),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $total,
            ];
        }

        return $normalized;
    }
}
