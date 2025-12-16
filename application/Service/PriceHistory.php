<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Price History Service
 * Provides functionality for tracking and logging price changes
 */
class Service_PriceHistory {

    /**
     * Database instance
     */
    private static $_db = null;

    /**
     * Get database instance
     */
    private static function get_db() {
        if (self::$_db === null) {
            self::$_db = Database::instance();
        }
        return self::$_db;
    }

    /**
     * Log a price change to the price_history table
     * 
     * @param array $data Price change data
     * @return int|false The inserted record ID or false on failure
     */
    public static function log_price_change($data) {
        try {
            $db = self::get_db();
            
            // Validate required fields
            if (empty($data['product_id']) || !isset($data['new_price'])) {
                throw new Exception('Product ID and new price are required');
            }

            // Prepare data for insertion
            $insert_data = array(
                'product_id' => (int) $data['product_id'],
                'new_price' => (float) $data['new_price'],
                'created_at' => date('Y-m-d H:i:s')
            );

            // Optional fields
            $optional_fields = array(
                'product_poid', 'customer_id', 'unity_id', 'segment_id', 
                'lead_id', 'old_price', 'price_type', 'change_reason', 
                'change_source', 'changed_by', 'api_response', 'effective_date'
            );

            foreach ($optional_fields as $field) {
                if (isset($data[$field]) && $data[$field] !== null) {
                    $insert_data[$field] = $data[$field];
                }
            }

            // Insert the record
            $result = DB::insert('price_history', array_keys($insert_data))
                ->values(array_values($insert_data))
                ->execute($db);

            return $result[0]; // Return the inserted ID
            
        } catch (Exception $e) {
            // Log the error
            Kohana::$log->add(Log::ERROR, 'Price history logging failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the last price for a specific product
     * 
     * @param int $product_id Product ID
     * @param int|null $customer_id Customer ID (optional)
     * @return array|null Last price record or null if not found
     */
    public static function get_last_price($product_id, $customer_id = null) {
        try {
            $db = self::get_db();
            
            $query = DB::select()
                ->from('price_history')
                ->where('product_id', '=', $product_id)
                ->order_by('created_at', 'DESC')
                ->limit(1);

            // Add customer filter if provided
            if ($customer_id !== null) {
                $query->where('customer_id', '=', $customer_id);
            } else {
                $query->where('customer_id', 'IS', null);
            }

            $result = $query->execute($db)->as_array();
            
            return !empty($result) ? $result[0] : null;
            
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Get last price failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get price history for a product
     * 
     * @param int $product_id Product ID
     * @param int|null $customer_id Customer ID (optional)
     * @param int $limit Number of records to return (default: 10)
     * @return array Price history records
     */
    public static function get_price_history($product_id, $customer_id = null, $limit = 10) {
        try {
            $db = self::get_db();
            
            $query = DB::select()
                ->from('price_history')
                ->where('product_id', '=', $product_id)
                ->order_by('created_at', 'DESC')
                ->limit($limit);

            // Add customer filter if provided
            if ($customer_id !== null) {
                $query->where('customer_id', '=', $customer_id);
            }

            return $query->execute($db)->as_array();
            
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Get price history failed: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Calculate price change percentage
     * 
     * @param float $old_price Old price
     * @param float $new_price New price
     * @return float|null Percentage change or null if calculation not possible
     */
    public static function calculate_price_change_percentage($old_price, $new_price) {
        if ($old_price === null || $old_price == 0) {
            return null;
        }
        
        return round((($new_price - $old_price) / $old_price) * 100, 2);
    }

    /**
     * Get price change direction
     * 
     * @param float|null $old_price Old price
     * @param float $new_price New price
     * @return string Direction: 'new', 'increase', 'decrease', 'unchanged'
     */
    public static function get_price_direction($old_price, $new_price) {
        if ($old_price === null) {
            return 'new';
        }
        
        if ($new_price > $old_price) {
            return 'increase';
        } elseif ($new_price < $old_price) {
            return 'decrease';
        } else {
            return 'unchanged';
        }
    }

    /**
     * Get current price from inventory table
     * 
     * @param int $product_id Product ID
     * @return float|null Current price or null if not found
     */
    public static function get_current_inventory_price($product_id) {
        try {
            $db = self::get_db();
            
            $result = DB::select('valor')
                ->from('inv')
                ->where('id', '=', $product_id)
                ->execute($db)
                ->as_array();
            
            return !empty($result) ? (float) $result[0]['valor'] : null;
            
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Get current inventory price failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Auto-log price change when inventory price is updated
     * This method should be called whenever a product price is updated
     * 
     * @param int $product_id Product ID
     * @param float $new_price New price
     * @param array $context Additional context (customer_id, lead_id, etc.)
     * @return bool Success status
     */
    public static function auto_log_price_change($product_id, $new_price, $context = array()) {
        // Get the last recorded price
        $last_price_record = self::get_last_price($product_id, isset($context['customer_id']) ? $context['customer_id'] : null);
        $old_price = $last_price_record ? $last_price_record['new_price'] : null;

        // Only log if price actually changed
        if ($old_price === null || $old_price != $new_price) {
            $log_data = array_merge($context, array(
                'product_id' => $product_id,
                'old_price' => $old_price,
                'new_price' => $new_price,
                'price_type' => isset($context['price_type']) ? $context['price_type'] : 'base',
                'change_source' => isset($context['change_source']) ? $context['change_source'] : 'manual',
                'change_reason' => isset($context['change_reason']) ? $context['change_reason'] : 'Price update'
            ));

            return self::log_price_change($log_data);
        }

        return true; // No change needed
    }

    /**
     * Get price changes for a customer within specified days
     * 
     * @param int $customer_id Customer ID
     * @param int $days Number of days to look back (default: 30)
     * @param int $limit Maximum number of records to return (default: 20)
     * @return array Price change records
     */
    public static function get_customer_price_changes($customer_id, $days = 30, $limit = 20) {
        try {
            $db = self::get_db();
            
            $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            
            $result = DB::select()
                ->from('price_history')
                ->where('customer_id', '=', $customer_id)
                ->where('created_at', '>=', $date_from)
                ->order_by('created_at', 'DESC')
                ->limit($limit)
                ->execute($db)
                ->as_array();
                
            return $result;
            
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Get customer price changes failed: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Compare current price with last price
     * 
     * @param int $product_id Product ID
     * @param int|null $customer_id Customer ID (optional)
     * @return array Comparison data
     */
    public static function compare_prices($product_id, $customer_id = null) {
        try {
            $db = self::get_db();
            
            // Get last two prices
            $query = DB::select()
                ->from('price_history')
                ->where('product_id', '=', $product_id)
                ->order_by('created_at', 'DESC')
                ->limit(2);
                
            if ($customer_id !== null) {
                $query->where('customer_id', '=', $customer_id);
            }
            
            $prices = $query->execute($db)->as_array();
            
            if (count($prices) < 2) {
                return array(
                    'has_comparison' => false,
                    'message' => 'Not enough price history for comparison'
                );
            }
            
            $current_price = floatval($prices[0]['new_price']);
            $previous_price = floatval($prices[1]['new_price']);
            
            $difference = $current_price - $previous_price;
            $percentage_change = $previous_price > 0 ? ($difference / $previous_price) * 100 : 0;
            
            return array(
                'has_comparison' => true,
                'current_price' => $current_price,
                'previous_price' => $previous_price,
                'difference' => $difference,
                'percentage_change' => round($percentage_change, 2),
                'trend' => $difference > 0 ? 'increase' : ($difference < 0 ? 'decrease' : 'stable'),
                'current_date' => $prices[0]['created_at'],
                'previous_date' => $prices[1]['created_at']
            );
            
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Compare prices failed: ' . $e->getMessage());
            return array(
                'has_comparison' => false,
                'message' => 'Error comparing prices: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get price trends and analytics for a product
     * 
     * @param int $product_id Product ID
     * @param int $days Number of days to analyze (default: 30)
     * @return array Trend analysis data
     */
    public static function get_price_trends($product_id, $days = 30) {
        try {
            $db = self::get_db();
            
            $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            
            $prices = DB::select()
                ->from('price_history')
                ->where('product_id', '=', $product_id)
                ->where('created_at', '>=', $date_from)
                ->order_by('created_at', 'ASC')
                ->execute($db)
                ->as_array();
                
            if (empty($prices)) {
                return array(
                    'has_data' => false,
                    'message' => 'No price data available for the specified period'
                );
            }
            
            $price_values = array_map(function($p) { return floatval($p['new_price']); }, $prices);
            $min_price = min($price_values);
            $max_price = max($price_values);
            $avg_price = array_sum($price_values) / count($price_values);
            
            // Calculate volatility (standard deviation)
            $variance = 0;
            foreach ($price_values as $price) {
                $variance += pow($price - $avg_price, 2);
            }
            $volatility = sqrt($variance / count($price_values));
            
            // Determine overall trend
            $first_price = floatval($prices[0]['new_price']);
            $last_price = floatval($prices[count($prices) - 1]['new_price']);
            $overall_change = $last_price - $first_price;
            $overall_percentage = $first_price > 0 ? ($overall_change / $first_price) * 100 : 0;
            
            return array(
                'has_data' => true,
                'period_days' => $days,
                'total_changes' => count($prices),
                'min_price' => $min_price,
                'max_price' => $max_price,
                'avg_price' => round($avg_price, 2),
                'volatility' => round($volatility, 2),
                'first_price' => $first_price,
                'last_price' => $last_price,
                'overall_change' => round($overall_change, 2),
                'overall_percentage' => round($overall_percentage, 2),
                'trend' => $overall_change > 0 ? 'upward' : ($overall_change < 0 ? 'downward' : 'stable'),
                'price_history' => $prices
            );
            
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Get price trends failed: ' . $e->getMessage());
            return array(
                'has_data' => false,
                'message' => 'Error analyzing price trends: ' . $e->getMessage()
            );
        }
    }
}