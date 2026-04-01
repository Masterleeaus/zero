<?php

namespace App\Services\AI;

use App\Models\Store;
use Illuminate\Support\Facades\DB;

class ChatQueryEngine
{
    /**
     * Map of natural language intents to safe SQL query templates.
     * All queries are SELECT only and MUST include store_id filter.
     */
    protected array $intentMap = [
        'today_sales' => [
            'patterns' => ['today sales', 'today\'s sales', 'sales today', 'how much sold today', 'today revenue'],
            'query' => "SELECT COUNT(*) as total_orders, COALESCE(SUM(total_price), 0) as total_revenue FROM orders WHERE store_id = ? AND DATE(created_at) = CURDATE() AND status != 'cancelled'",
            'format' => 'You had {total_orders} orders today with total revenue of ৳{total_revenue}.',
        ],
        'yesterday_sales' => [
            'patterns' => ['yesterday sales', 'yesterday\'s sales', 'sales yesterday'],
            'query' => "SELECT COUNT(*) as total_orders, COALESCE(SUM(total_price), 0) as total_revenue FROM orders WHERE store_id = ? AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND status != 'cancelled'",
            'format' => 'Yesterday you had {total_orders} orders with total revenue of ৳{total_revenue}.',
        ],
        'monthly_sales' => [
            'patterns' => ['this month sales', 'monthly sales', 'sales this month', 'month revenue'],
            'query' => "SELECT COUNT(*) as total_orders, COALESCE(SUM(total_price), 0) as total_revenue FROM orders WHERE store_id = ? AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status != 'cancelled'",
            'format' => 'This month: {total_orders} orders, ৳{total_revenue} total revenue.',
        ],
        'total_products' => [
            'patterns' => ['how many products', 'total products', 'product count', 'number of products'],
            'query' => "SELECT COUNT(*) as total FROM products WHERE store_id = ?",
            'format' => 'You currently have {total} products in your store.',
        ],
        'low_stock' => [
            'patterns' => ['low stock', 'out of stock', 'stock alert', 'need restock'],
            'query' => "SELECT name, stock FROM products WHERE store_id = ? AND stock <= 5 AND stock > 0 AND status = 'active' ORDER BY stock ASC LIMIT 10",
            'format' => 'list',
        ],
        'top_customers' => [
            'patterns' => ['top customers', 'best customers', 'vip customers', 'loyal customers'],
            'query' => "SELECT customer_name, COUNT(*) as orders, SUM(total_price) as total_spent FROM orders WHERE store_id = ? AND status != 'cancelled' GROUP BY customer_name ORDER BY total_spent DESC LIMIT 10",
            'format' => 'list',
        ],
        'top_products' => [
            'patterns' => ['top products', 'best selling', 'most sold', 'popular products'],
            'query' => "SELECT p.name, SUM(oi.quantity) as total_sold FROM order_items oi JOIN orders o ON o.id = oi.order_id JOIN products p ON p.id = oi.product_id WHERE o.store_id = ? AND o.status != 'cancelled' GROUP BY p.name ORDER BY total_sold DESC LIMIT 10",
            'format' => 'list',
        ],
        'pending_orders' => [
            'patterns' => ['pending orders', 'unshipped orders', 'orders to ship', 'waiting orders'],
            'query' => "SELECT COUNT(*) as total FROM orders WHERE store_id = ? AND status = 'pending'",
            'format' => 'You have {total} pending orders awaiting processing.',
        ],
        'total_customers' => [
            'patterns' => ['how many customers', 'total customers', 'customer count'],
            'query' => "SELECT COUNT(DISTINCT user_id) as total FROM orders WHERE store_id = ? AND user_id IS NOT NULL",
            'format' => 'You have {total} unique customers.',
        ],
        'total_revenue' => [
            'patterns' => ['total revenue', 'all time revenue', 'lifetime revenue', 'total earnings'],
            'query' => "SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE store_id = ? AND status != 'cancelled'",
            'format' => 'Your all-time revenue is ৳{total}.',
        ],
    ];

    /**
     * Process a natural language query and return a safe response.
     */
    public function ask(string $question, int $storeId): array
    {
        $question = strtolower(trim($question));

        foreach ($this->intentMap as $intentKey => $intent) {
            foreach ($intent['patterns'] as $pattern) {
                if (str_contains($question, $pattern)) {
                    return $this->executeIntent($intent, $storeId);
                }
            }
        }

        return [
            'success' => false,
            'message' => "I'm not sure how to answer that yet. Try asking about: today's sales, top products, low stock, pending orders, or top customers.",
            'suggestions' => ['Today\'s sales', 'Top products', 'Low stock items', 'Pending orders', 'Top customers'],
        ];
    }

    /**
     * Execute a matched intent query SAFELY — always bound to store_id.
     */
    protected function executeIntent(array $intent, int $storeId): array
    {
        try {
            // CRITICAL SECURITY: Only SELECT queries allowed + store_id injected via binding
            $results = DB::select($intent['query'], [$storeId]);

            if ($intent['format'] === 'list') {
                return [
                    'success' => true,
                    'message' => 'Here are the results:',
                    'data' => array_map(fn($row) => (array) $row, $results),
                    'type' => 'table',
                ];
            }

            // Single row result — format with template
            $row = (array) ($results[0] ?? []);
            $message = $intent['format'];
            foreach ($row as $key => $value) {
                $message = str_replace('{' . $key . '}', number_format((float) $value, 2), $message);
            }

            return [
                'success' => true,
                'message' => $message,
                'data' => $row,
                'type' => 'text',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Sorry, I encountered an error processing your query.',
            ];
        }
    }
}
