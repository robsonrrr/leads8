<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Customer Cache Service
 * Provides caching functionality for customer data and related operations
 */
class Service_CustomerCache {

    /**
     * Cache instances
     */
    private static $_customer_cache = null;
    private static $_metrics_cache = null;
    private static $_reference_cache = null;

    /**
     * Cache TTL constants (in seconds)
     */
    const CUSTOMER_DATA_TTL = 900;    // 15 minutes
    const METRICS_TTL = 3600;         // 1 hour
    const REFERENCE_TTL = 86400;      // 24 hours

    /**
     * Get customer data cache instance
     */
    public static function get_customer_cache() {
        if (self::$_customer_cache === null) {
            self::$_customer_cache = Cache::instance('customer_data');
        }
        return self::$_customer_cache;
    }

    /**
     * Get metrics cache instance
     */
    public static function get_metrics_cache() {
        if (self::$_metrics_cache === null) {
            self::$_metrics_cache = Cache::instance('aggregated_metrics');
        }
        return self::$_metrics_cache;
    }

    /**
     * Get reference data cache instance
     */
    public static function get_reference_cache() {
        if (self::$_reference_cache === null) {
            self::$_reference_cache = Cache::instance('reference_data');
        }
        return self::$_reference_cache;
    }

    /**
     * Generate cache key for customer data
     */
    public static function generate_customer_key($search = '', $filters = array(), $start = 0, $length = 10, $order_column = 'cliente_nome', $order_dir = 'asc') {
        $filter_data = array(
            'search' => $search,
            'filters' => $filters,
            'start' => $start,
            'length' => $length,
            'order_column' => $order_column,
            'order_dir' => $order_dir
        );
        
        $filter_hash = md5(serialize($filter_data));
        return "customer_data:{$filter_hash}";
    }

    /**
     * Generate cache key for customer count
     */
    public static function generate_count_key($search = '', $filters = array()) {
        $filter_data = array(
            'search' => $search,
            'filters' => $filters
        );
        
        $filter_hash = md5(serialize($filter_data));
        return "customer_count:{$filter_hash}";
    }

    /**
     * Generate generic cache key
     */
    public static function generate_cache_key($prefix, $params = array()) {
        $param_hash = md5(serialize($params));
        return "{$prefix}:{$param_hash}";
    }

    /**
     * Cache customer data
     */
    public static function set_customer_data($key, $data) {
        try {
            $cache = self::get_customer_cache();
            return $cache->set($key, $data, self::CUSTOMER_DATA_TTL);
        } catch (Exception $e) {
            error_log('Customer cache set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cached customer data
     */
    public static function get_customer_data($key) {
        try {
            $cache = self::get_customer_cache();
            return $cache->get($key, false);
        } catch (Exception $e) {
            error_log('Customer cache get error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cache customer count
     */
    public static function set_customer_count($key, $count) {
        try {
            $cache = self::get_customer_cache();
            return $cache->set($key, $count, self::CUSTOMER_DATA_TTL);
        } catch (Exception $e) {
            error_log('Customer count cache set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cached customer count
     */
    public static function get_customer_count($key) {
        try {
            $cache = self::get_customer_cache();
            return $cache->get($key, false);
        } catch (Exception $e) {
            error_log('Customer count cache get error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cache total customer count
     */
    public static function set_total_count($count) {
        try {
            $cache = self::get_customer_cache();
            return $cache->set('customer_total_count', $count, self::CUSTOMER_DATA_TTL);
        } catch (Exception $e) {
            error_log('Total count cache set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cached total customer count
     */
    public static function get_total_count() {
        try {
            $cache = self::get_customer_cache();
            return $cache->get('customer_total_count', false);
        } catch (Exception $e) {
            error_log('Total count cache get error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cache monthly sales data
     */
    public static function set_monthly_sales($year, $month, $data) {
        try {
            $cache = self::get_metrics_cache();
            $key = "sales_monthly:{$year}:{$month}";
            return $cache->set($key, $data, self::METRICS_TTL);
        } catch (Exception $e) {
            error_log('Monthly sales cache set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cached monthly sales data
     */
    public static function get_monthly_sales($year, $month) {
        try {
            $cache = self::get_metrics_cache();
            $key = "sales_monthly:{$year}:{$month}";
            return $cache->get($key, false);
        } catch (Exception $e) {
            error_log('Monthly sales cache get error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cache yearly sales data
     */
    public static function set_yearly_sales($year, $data) {
        try {
            $cache = self::get_metrics_cache();
            $key = "sales_yearly:{$year}";
            return $cache->set($key, $data, self::METRICS_TTL);
        } catch (Exception $e) {
            error_log('Yearly sales cache set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cached yearly sales data
     */
    public static function get_yearly_sales($year) {
        try {
            $cache = self::get_metrics_cache();
            $key = "sales_yearly:{$year}";
            return $cache->get($key, false);
        } catch (Exception $e) {
            error_log('Yearly sales cache get error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cache segment distribution data
     */
    public static function set_segment_distribution($data) {
        try {
            $cache = self::get_metrics_cache();
            return $cache->set('segment_distribution', $data, self::METRICS_TTL);
        } catch (Exception $e) {
            error_log('Segment distribution cache set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cached segment distribution data
     */
    public static function get_segment_distribution() {
        try {
            $cache = self::get_metrics_cache();
            return $cache->get('segment_distribution', false);
        } catch (Exception $e) {
            error_log('Segment distribution cache get error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cache RFM distribution data
     */
    public static function set_rfm_distribution($data) {
        try {
            $cache = self::get_metrics_cache();
            return $cache->set('rfm_distribution', $data, self::METRICS_TTL);
        } catch (Exception $e) {
            error_log('RFM distribution cache set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cached RFM distribution data
     */
    public static function get_rfm_distribution() {
        try {
            $cache = self::get_metrics_cache();
            return $cache->get('rfm_distribution', false);
        } catch (Exception $e) {
            error_log('RFM distribution cache get error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cache states list
     */
    public static function set_states_list($data) {
        try {
            $cache = self::get_reference_cache();
            return $cache->set('states_list', $data, self::REFERENCE_TTL);
        } catch (Exception $e) {
            error_log('States list cache set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cached states list
     */
    public static function get_states_list() {
        try {
            $cache = self::get_reference_cache();
            return $cache->get('states_list', false);
        } catch (Exception $e) {
            error_log('States list cache get error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cache segments list
     */
    public static function set_segments_list($data) {
        try {
            $cache = self::get_reference_cache();
            return $cache->set('segments_list', $data, self::REFERENCE_TTL);
        } catch (Exception $e) {
            error_log('Segments list cache set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cached segments list
     */
    public static function get_segments_list() {
        try {
            $cache = self::get_reference_cache();
            return $cache->get('segments_list', false);
        } catch (Exception $e) {
            error_log('Segments list cache get error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cache vendors list
     */
    public static function set_vendors_list($data) {
        try {
            $cache = self::get_reference_cache();
            return $cache->set('vendors_list', $data, self::REFERENCE_TTL);
        } catch (Exception $e) {
            error_log('Vendors list cache set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cached vendors list
     */
    public static function get_vendors_list() {
        try {
            $cache = self::get_reference_cache();
            return $cache->get('vendors_list', false);
        } catch (Exception $e) {
            error_log('Vendors list cache get error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all customer data cache
     */
    public static function clear_customer_cache() {
        try {
            $cache = self::get_customer_cache();
            return $cache->delete_all();
        } catch (Exception $e) {
            error_log('Customer cache clear error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all metrics cache
     */
    public static function clear_metrics_cache() {
        try {
            $cache = self::get_metrics_cache();
            return $cache->delete_all();
        } catch (Exception $e) {
            error_log('Metrics cache clear error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all reference data cache
     */
    public static function clear_reference_cache() {
        try {
            $cache = self::get_reference_cache();
            return $cache->delete_all();
        } catch (Exception $e) {
            error_log('Reference cache clear error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all caches
     */
    public static function clear_all_cache() {
        $results = array(
            'customer' => self::clear_customer_cache(),
            'metrics' => self::clear_metrics_cache(),
            'reference' => self::clear_reference_cache()
        );
        
        return $results;
    }

    /**
     * Invalidate cache when customer data is modified
     * Call this method after any customer data update/insert/delete operations
     */
    public static function invalidate_customer_data($customer_id = null) {
        try {
            $customer_cache = self::get_customer_cache();
            $metrics_cache = self::get_metrics_cache();
            
            if ($customer_id) {
                // Clear specific customer-related caches
                $pattern_keys = [
                    'customer_data_*',
                    'customer_count_*',
                    'total_count',
                    'monthly_sales_*',
                    'annual_sales_*'
                ];
                
                foreach ($pattern_keys as $pattern) {
                    // Note: This is a simplified approach. In production,
                    // you might want to implement a more sophisticated
                    // cache key tracking system
                    $customer_cache->delete_all();
                    $metrics_cache->delete_all();
                    break; // Delete all for now since we can't pattern match easily
                }
            } else {
                // Clear all customer and metrics caches
                $customer_cache->delete_all();
                $metrics_cache->delete_all();
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Customer cache invalidation error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Invalidate reference data cache
     * Call this method when reference data (like segments, states) is modified
     */
    public static function invalidate_reference_data() {
        try {
            $reference_cache = self::get_reference_cache();
            $reference_cache->delete_all();
            return true;
        } catch (Exception $e) {
            error_log('Reference cache invalidation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cache statistics
     */
    public static function get_cache_stats() {
        $stats = array(
            'customer_cache' => array(
                'enabled' => true,
                'ttl' => self::CUSTOMER_DATA_TTL
            ),
            'metrics_cache' => array(
                'enabled' => true,
                'ttl' => self::METRICS_TTL
            ),
            'reference_cache' => array(
                'enabled' => true,
                'ttl' => self::REFERENCE_TTL
            )
        );
        
        return $stats;
    }
}