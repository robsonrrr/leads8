<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Price History API Controller
 * Provides endpoints for retrieving price history and last price information
 */
class Controller_Api_Pricehistory extends Controller_Website {

    /**
     * Simple test to verify controller is working
     */
    public function action_index()
    {
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode(array(
            'success' => true,
            'message' => 'Price History API is working',
            'timestamp' => date('Y-m-d H:i:s')
        )));
    }

    /**
     * Get price history for a specific product
     * GET /api/pricehistory/product/{product_id}
     */
    public function action_product()
    {
        $product_id = $this->request->param('id');
        
        if (!$product_id) {
            $this->response->status(400);
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => false,
                'error' => 'Product ID is required'
            )));
            return;
        }

        try {
            // Get price history from database
            $query = "SELECT price, date_created, customer_id 
                     FROM orders 
                     WHERE product_id = ? 
                     ORDER BY date_created DESC 
                     LIMIT 50";
            
            $result = DB::query(Database::SELECT, $query)
                ->parameters(array($product_id))
                ->execute();

            $price_history = array();
            foreach ($result as $row) {
                $price_history[] = array(
                    'price' => floatval($row['price']),
                    'date' => $row['date_created'],
                    'customer_id' => $row['customer_id']
                );
            }

            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => true,
                'product_id' => $product_id,
                'price_history' => $price_history
            )));

        } catch (Exception $e) {
            $this->response->status(500);
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            )));
        }
    }

    /**
     * Get last price for a specific product
     * GET /api/pricehistory/lastprice/{product_id}
     */
    public function action_lastprice()
    {
        $product_id = $this->request->param('id');
        
        if (!$product_id) {
            $this->response->status(400);
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => false,
                'error' => 'Product ID is required'
            )));
            return;
        }

        try {
            // Get last price from database
            $query = "SELECT price, date_created, customer_id 
                     FROM orders 
                     WHERE product_id = ? 
                     ORDER BY date_created DESC 
                     LIMIT 1";
            
            $result = DB::query(Database::SELECT, $query)
                ->parameters(array($product_id))
                ->execute()
                ->current();

            if ($result) {
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode(array(
                    'success' => true,
                    'product_id' => $product_id,
                    'last_price' => floatval($result['price']),
                    'last_sale_date' => $result['date_created'],
                    'customer_id' => $result['customer_id']
                )));
            } else {
                $this->response->status(404);
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode(array(
                    'success' => false,
                    'error' => 'No price history found for this product'
                )));
            }

        } catch (Exception $e) {
            $this->response->status(500);
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            )));
        }
    }

    /**
     * Get price history for a specific customer
     * GET /api/pricehistory/customer/{customer_id}
     */
    public function action_customer()
    {
        $customer_id = $this->request->param('id');
        
        if (!$customer_id) {
            $this->response->status(400);
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => false,
                'error' => 'Customer ID is required'
            )));
            return;
        }

        try {
            // Get customer's purchase history
            $query = "SELECT product_id, price, date_created 
                     FROM orders 
                     WHERE customer_id = ? 
                     ORDER BY date_created DESC 
                     LIMIT 100";
            
            $result = DB::query(Database::SELECT, $query)
                ->parameters(array($customer_id))
                ->execute();

            $purchase_history = array();
            foreach ($result as $row) {
                $purchase_history[] = array(
                    'product_id' => $row['product_id'],
                    'price' => floatval($row['price']),
                    'date' => $row['date_created']
                );
            }

            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => true,
                'customer_id' => $customer_id,
                'purchase_history' => $purchase_history
            )));

        } catch (Exception $e) {
            $this->response->status(500);
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            )));
        }
    }

    /**
     * Compare current price with last price
     * GET /api/pricehistory/compare/{product_id}
     */
    public function action_compare()
    {
        $product_id = $this->request->param('id');
        
        if (!$product_id) {
            $this->response->status(400);
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => false,
                'error' => 'Product ID is required'
            )));
            return;
        }

        try {
            // Get last two prices
            $query = "SELECT price, date_created 
                     FROM orders 
                     WHERE product_id = ? 
                     ORDER BY date_created DESC 
                     LIMIT 2";
            
            $result = DB::query(Database::SELECT, $query)
                ->parameters(array($product_id))
                ->execute();

            $prices = array();
            foreach ($result as $row) {
                $prices[] = array(
                    'price' => floatval($row['price']),
                    'date' => $row['date_created']
                );
            }

            if (count($prices) >= 2) {
                $current_price = $prices[0]['price'];
                $previous_price = $prices[1]['price'];
                $change = $current_price - $previous_price;
                $percentage_change = $previous_price > 0 ? ($change / $previous_price) * 100 : 0;

                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode(array(
                    'success' => true,
                    'product_id' => $product_id,
                    'current_price' => $current_price,
                    'previous_price' => $previous_price,
                    'price_change' => $change,
                    'percentage_change' => round($percentage_change, 2),
                    'trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable')
                )));
            } else {
                $this->response->status(404);
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode(array(
                    'success' => false,
                    'error' => 'Insufficient price history for comparison'
                )));
            }

        } catch (Exception $e) {
            $this->response->status(500);
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            )));
        }
    }

    /**
     * Get price trends for a product
     * GET /api/pricehistory/trends/{product_id}
     */
    public function action_trends()
    {
        $product_id = $this->request->param('id');
        
        if (!$product_id) {
            $this->response->status(400);
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => false,
                'error' => 'Product ID is required'
            )));
            return;
        }

        try {
            // Get price trends over time
            $query = "SELECT AVG(price) as avg_price, 
                            MIN(price) as min_price, 
                            MAX(price) as max_price,
                            COUNT(*) as sales_count,
                            DATE(date_created) as sale_date
                     FROM orders 
                     WHERE product_id = ? 
                     AND date_created >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                     GROUP BY DATE(date_created)
                     ORDER BY sale_date DESC";
            
            $result = DB::query(Database::SELECT, $query)
                ->parameters(array($product_id))
                ->execute();

            $trends = array();
            foreach ($result as $row) {
                $trends[] = array(
                    'date' => $row['sale_date'],
                    'avg_price' => floatval($row['avg_price']),
                    'min_price' => floatval($row['min_price']),
                    'max_price' => floatval($row['max_price']),
                    'sales_count' => intval($row['sales_count'])
                );
            }

            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => true,
                'product_id' => $product_id,
                'period' => 'Last 30 days',
                'trends' => $trends
            )));

        } catch (Exception $e) {
            $this->response->status(500);
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            )));
        }
    }

    /**
     * Bulk import price history data
     * POST /api/pricehistory/bulk-import
     */
    public function action_bulk_import()
    {
        if ($this->request->method() !== 'POST') {
            $this->response->status(405);
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => false,
                'error' => 'Method not allowed. Use POST.'
            )));
            return;
        }

        try {
            $input = json_decode($this->request->body(), true);
            
            if (!$input || !isset($input['price_data']) || !is_array($input['price_data'])) {
                $this->response->status(400);
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode(array(
                    'success' => false,
                    'error' => 'Invalid input format. Expected JSON with price_data array.'
                )));
                return;
            }

            $imported_count = 0;
            $errors = array();

            foreach ($input['price_data'] as $index => $data) {
                if (!isset($data['product_id']) || !isset($data['price']) || !isset($data['customer_id'])) {
                    $errors[] = "Row $index: Missing required fields (product_id, price, customer_id)";
                    continue;
                }

                try {
                    $query = "INSERT INTO orders (product_id, price, customer_id, date_created) 
                             VALUES (?, ?, ?, NOW())";
                    
                    DB::query(Database::INSERT, $query)
                        ->parameters(array($data['product_id'], $data['price'], $data['customer_id']))
                        ->execute();
                    
                    $imported_count++;
                } catch (Exception $e) {
                    $errors[] = "Row $index: " . $e->getMessage();
                }
            }

            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => true,
                'imported_count' => $imported_count,
                'total_rows' => count($input['price_data']),
                'errors' => $errors
            )));

        } catch (Exception $e) {
            $this->response->status(500);
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(array(
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            )));
        }
    }
}