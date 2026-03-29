<?php

class Cart {
    private array $items = [];

    public function add(Product $product, int $quantity = 1): void {
        $productId = $product->getId();

        if (isset($this->items[$productId])) {
            $this->items[$productId]['quantity'] += $quantity;
        } else {
            $this->items[$productId] = [
                'product' => $product,
                'quantity' => $quantity
            ];
        }
    }

    public function remove(int $productId): void {
        if (isset($this->items[$productId])) {
            unset($this->items[$productId]);
        }
    }

    public function getItems(): array {
        return $this->items;
    }

    public function getTotal(): float {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }
        return $total;
    }

    public function getFormattedTotal(): string {
        return number_format($this->getTotal(), 2, '.', ' ') . ' руб.';
    }

    public function clear(): void {
        $this->items = [];
    }
}