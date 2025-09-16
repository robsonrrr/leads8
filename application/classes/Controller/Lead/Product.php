<?php

class Controller_Lead_Product extends Controller_Lead_Base {

    public function before()
    {
        parent::before();
    }

    public function action_index()
    {
        $productID  = $this->request->param('id');
        $segmentID  = $this->request->param('segment');
        $customerId = $this->request->param('complement');

        //s($productID,$segmentID,$customerId);
        // die();

        if ( ! $productID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $productID</h3>'));

        $produto = self::get( $productID, $segmentID );

        // public function get_stock( $id, $unity = false )
        // public function get_price( $product, $unity = false, $customer, $terms= false , $lead = false, $segmentID = false)

        $produto['price'] = $this->get_price( $productID, false, $customerId, false, false, $segmentID);
        $produto['stock'] = $this->get_stock( $productID, false);

        $produto['segmentoPOID'] = $segmentID;

        if ($this->request->query('ajax')) {
            $this->response->status(200); // Method Not Allowed
            $this->response->body(json_encode($produto));
            return;
        }

        //d($produto);

        $template = $this->render( 'product/info', $produto );
    }


    /**
     * Retrieves product details by ID
     *
     * @param int $productID Product ID
     * @return array|bool Product details as array or false if not found
     */
    public function get($productID, $segmentID)
    {
        $sql = sprintf(
            "SELECT
                inv.id as produtoPOID,
                inv.nome as produtoNome,
                inv.modelo as produtoModelo,
                inv.peso as produtoPeso,
                inv.marca as produtoMarca,
                inv.codebar as produtoCodigoBarra,
                packing.packing as produtoEmbalagem,
                produtos.ncm as produtoNCM,
                catalog.map->>'$.descriptions.name' as ecommerce_name,
                catalog.map->>'$.descriptions.descriptions' as ecommerce_descriptions,
                catalog.map->>'$.descriptions.resume' as ecommerce_resume,
                catalog.map->>'$.descriptions.complement' as ecommerce_complement,
                catalog.map->>'$.descriptions.consumer' as ecommerce_dealer,
                catalog.map->>'$.descriptions.short' as ecommerce_short,
                catalog.map->>'$.descriptions.document' as ecommerce_document,
                catalog.map->>'$.descriptions.video' as ecommerce_video,
                catalog.map->>'$.machine_functions' as machine_functions,
                catalog.map->>'$.machine_aplications' as machine_aplications,
                catalog.map->>'$.crossselling' as product_similar,
                catalog.map->>'$.parts' as product_parts,
                categories.c_name as categoriaNome,
                categories.c_title as categoriaTitulo,
                segments.s_name as segmentoNome,
                segments.s_title as segmentoTitulo
            FROM mak.inv
            LEFT JOIN Catalogo.packing ON (packing.id = inv.embalagem)
            LEFT JOIN mak.produtos ON (produtos.id = inv.idcf)
            LEFT JOIN Catalogo.catalog ON (catalog.product_id = inv.id)
            LEFT JOIN Catalogo.categories ON (categories.id = catalog.category_id)
            LEFT JOIN Catalogo.segments ON (segments.id = catalog.segment_id)
            WHERE 1=1
                AND catalog.product_id = %s
                AND catalog.segment_id = %s
            ORDER BY inv.id DESC",
            $productID,
            $segmentID
        );

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        if (!empty($response[0])) {
            $product = $response[0];
            // Fetch all product features without filtering
            $product['features'] = $this->getProductFeatures($product['produtoPOID']);

            // Load machine functions and applications
            $product = $this->loadMachineFunctionsAndApplications($product);

            return $product;
        }

        return false;
    }

    /**
     * Retrieves product features
     *
     * @param int $productId Product ID
     * @param int $cacheMinutes Cache duration in minutes
     * @return array Array of product features
     */
    public static function getProductFeatures($productId, $cacheMinutes = 60)
    {
        $cacheKey = 'product_features_' . $productId;

        // Check if cache is disabled
        if (!KO7::$config->load('cache.enabled')) {
            Cache::instance()->delete($cacheKey);
        }

        // Use KO7's Cache module
        $cache = Cache::instance();
        $features = $cache->get($cacheKey);

        if ($features === NULL) {
            $query = DB::select(
                'catalog_features.id',
                'catalog_features.product_id',
                'catalog_features.feature',
                'catalog_features.attributes',
                array('features.f_name', 'feature_name'),
                array('features.f_desc', 'feature_description'),
                array('features_attributes.attribute', 'attribute_name')
            )
                ->from('Catalogo.catalog_features')
                ->where('catalog_features.product_id', '=', $productId)
                ->join('Catalogo.features', 'LEFT')
                ->on('catalog_features.feature', '=', 'features.id')
                ->join('Catalogo.features_attributes', 'LEFT')
                ->on('catalog_features.attributes', '=', 'features_attributes.id')
                ->execute()
                ->as_array();

            // Organize features into a structured array
            $result = [];
            foreach ($query as $feature) {
                $result[] = [
                    'id' => $feature['id'],
                    'feature_id' => $feature['feature'],
                    'feature_name' => $feature['feature_name'],
                    'feature_description' => $feature['feature_description'],
                    'attribute_id' => $feature['attributes'],
                    'attribute_name' => $feature['attribute_name']
                ];
            }

            // Store in cache
            $cache->set($cacheKey, $result, $cacheMinutes * 60);

            return $result;
        }

        return $features;
    }

    /**
     * Centralized method to load machine functions and applications for a product
     *
     * @param array $product Product array with machine_functions and machine_aplications fields
     * @return array Product array with functions and applications added
     */
    protected static function loadMachineFunctionsAndApplications($product)
    {
        // Load machine functions
        if (!empty($product['machine_functions'])) {
            $functionIds = json_decode($product['machine_functions'], true);
            if (!empty($functionIds) && is_array($functionIds)) {
                $functions = DB::select('id', array('ap_seo', 'seo'), array('ap_name', 'name'), array('ap_type', 'type'))
                    ->from('Catalogo.machine_functions')
                    ->where('id', 'IN', $functionIds)
                    ->execute()
                    ->as_array();
                $product['functions'] = $functions;

                // Group functions by type
                $functionsByType = [];
                foreach ($functions as $function) {
                    $functionsByType[$function['type']][] = $function;
                }
                $product['functionsByType'] = $functionsByType;
            } else {
                $product['functions'] = [];
                $product['functionsByType'] = [];
            }
        } else {
            $product['functions'] = [];
            $product['functionsByType'] = [];
        }

        // Load machine applications
        if (!empty($product['machine_aplications'])) {
            $applicationIds = json_decode($product['machine_aplications'], true);
            if (!empty($applicationIds) && is_array($applicationIds)) {
                $applications = DB::select('id', array('ap_seo', 'seo'), array('ap_name', 'name'))
                    ->from('Catalogo.machine_aplications')
                    ->where('id', 'IN', $applicationIds)
                    ->execute()
                    ->as_array();
                $product['applications'] = $applications;
            } else {
                $product['applications'] = [];
            }
        } else {
            $product['applications'] = [];
        }

        // Load machine features
        if (!empty($product['machine_features'])) {
            $featureIds = json_decode($product['machine_features'], true);
            if (!empty($featureIds) && is_array($featureIds)) {
                $features = DB::select('id', 'title', 'image_url', 'description_en', 'description_pt')
                    ->from('Catalogo.machine_features')
                    ->where('id', 'IN', $featureIds)
                    ->execute()
                    ->as_array();
                $product['mfeatures'] = $features;
            } else {
                $product['mfeatures'] = [];
            }
        } else {
            $product['mfeatures'] = [];
        }

        return $product;
    }

}