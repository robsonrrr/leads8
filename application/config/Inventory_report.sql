CREATE VIEW InventoryReport AS
SELECT
    inv.id AS product_id,
    inv.id AS isbn,
    inv.modelo AS model_code,
    inv.marca AS brand_code,
    (stock.stock_tatuape + stock.stock_store + stock.stock_barra_funda + stock.stock_blumenau + stock.stock_blumenau_ttd + stock.stock_blumenau_ttd2 +
     stock.reserved_tatuape + stock.reserved_store + stock.reserved_barra_funda + stock.reserved_blumenau) AS total_stock,
    stock.stock_barra_funda,
    stock.stock_blumenau_ttd AS stock_blumenau,
    (stock.stock_blumenau + stock.stock_blumenau_ttd) AS stock_blumenau_total,
    stock.stock_tatuape,
    inv.Fob AS fob_price,
    inv.Revenda AS resale_price,
    inv.fob * 10 AS resale_price_100,
    inv.fob * 9.5 AS resale_price_95,
    inv.fob * 9 AS resale_price_90,
    inv.fob * 8.5 AS resale_price_85,
    inv.vip AS vip,
    inv.rfm AS rfm,
    inv.abc AS abc,
    sales.sales_last_0_days,
    sales.sales_last_7_days,
    sales.sales_last_30_days,
    sales.sales_last_60_days,
    sales.sales_last_90_days,
    sales.sales_last_365_days,
    sales.sales_current_year,
    sales.sales_forecast_year,
    (sales.sales_forecast_year - sales.sales_current_year) AS sales_year_diff,
    sales.sales_year_2024,
    sales.sales_year_2023,
    sales.sales_year_2022,
    (stock.stock_tatuape + stock.stock_store + stock.stock_barra_funda + stock.stock_blumenau + stock.stock_blumenau_ttd) * inv.fob * 1.27 * 5.40 AS total_stock_value,
    CONCAT(inv.modelo, ' (', IF(inv.Embalagem = 1, 'cx', 'ind'), ')') AS model_description,
    quality.Quality AS quality,
    IF(inv.Embalagem = 1, 'ind. box', 'bulk') AS packing_type,
    last_sale.last_sale_days,
    ship.age_days,
    ROUND((stock.stock_tatuape + stock.stock_store + stock.stock_barra_funda + stock.stock_blumenau + stock.stock_blumenau_ttd) / NULLIF(sales.avg_sales_90_days, 0), 0) AS stock_life_months,
    ship.in_transit,
    (ship.total_next - ship.in_transit) AS in_factory,
    ship.total_next,
    '' AS buffer_3,
    '' AS buffer_6,
    '' AS buffer_12,
    inv.Peso AS weight,
    inv.PesoSet AS weight_set,
    inv.VolumeSet AS volume_set,
    inv.estoque_transito AS transit_stock,
    inv.nome AS product_name,
    inv.estoque_min AS min_stock,
    inv.estoque_max AS max_stock,
    inv.estoque_protecao AS safety_stock,
    sales.avg_sales_90_days,
    sales.sales_current_year AS sales_year,
    inv.memoh AS notes,
    inv.fob AS suggested_price,
    '' AS total_weight,
    inv.eDescricao AS description,
    inv.mARCA AS brand_name,
    inv.CostIndex AS cost_index,
    sales.avg_sales_value_year,
    inv.volume AS volume,
    inv.volumeset AS volume_set_alt,
    ship.forecast_days,
    ship.scheduled,
    sales.sales_last_0_days AS sales_last_day
FROM inv
LEFT JOIN (
    SELECT
        e.ProdutoPOID AS product_id,
        SUM(CASE WHEN e.table_schema = 'mak_0109' THEN e.EstoqueDisponivel ELSE 0 END) AS stock_tatuape,
        SUM(CASE WHEN e.table_schema = 'mak_0370' THEN e.EstoqueDisponivel ELSE 0 END) AS stock_store,
        SUM(CASE WHEN e.table_schema = 'mak_0885' THEN e.EstoqueDisponivel ELSE 0 END) AS stock_barra_funda,
        SUM(CASE WHEN e.table_schema = 'mak_0966' THEN e.EstoqueDisponivel ELSE 0 END) AS stock_blumenau,
        SUM(CASE WHEN e.table_schema = 'mak_0966' AND e.table_name = 'Estoque_TTD_1' THEN e.EstoqueDisponivel ELSE 0 END) AS stock_blumenau_ttd,
        SUM(CASE WHEN e.table_schema = 'mak_0613' AND e.table_name = 'Estoque_TTD_1' THEN e.EstoqueDisponivel ELSE 0 END) AS stock_blumenau_ttd2,
        SUM(CASE WHEN e.table_schema = 'mak_0109' THEN e.EstoqueReservado ELSE 0 END) AS reserved_tatuape,
        SUM(CASE WHEN e.table_schema = 'mak_0370' THEN e.EstoqueReservado ELSE 0 END) AS reserved_store,
        SUM(CASE WHEN e.table_schema = 'mak_0885' THEN e.EstoqueReservado ELSE 0 END) AS reserved_barra_funda,
        SUM(CASE WHEN e.table_schema = 'mak_0966' THEN e.EstoqueReservado ELSE 0 END) AS reserved_blumenau
    FROM (
        SELECT 'mak_0109' AS table_schema, 'Estoque' AS table_name, ProdutoPOID, EstoqueDisponivel, EstoqueReservado
        FROM mak_0109.Estoque
        UNION ALL
        SELECT 'mak_0370' AS table_schema, 'Estoque' AS table_name, ProdutoPOID, EstoqueDisponivel, EstoqueReservado
        FROM mak_0370.Estoque
        UNION ALL
        SELECT 'mak_0885' AS table_schema, 'Estoque' AS table_name, ProdutoPOID, EstoqueDisponivel, EstoqueReservado
        FROM mak_0885.Estoque
        UNION ALL
        SELECT 'mak_0966' AS table_schema, 'Estoque' AS table_name, ProdutoPOID, EstoqueDisponivel, EstoqueReservado
        FROM mak_0966.Estoque
        UNION ALL
        SELECT 'mak_0966' AS table_schema, 'Estoque_TTD_1' AS table_name, ProdutoPOID, EstoqueDisponivel, 0 AS EstoqueReservado
        FROM mak_0966.Estoque_TTD_1
        UNION ALL
        SELECT 'mak_0613' AS table_schema, 'Estoque_TTD_1' AS table_name, ProdutoPOID, EstoqueDisponivel, 0 AS EstoqueReservado
        FROM mak_0613.Estoque_TTD_1
    ) e
    GROUP BY e.ProdutoPOID
) stock ON stock.product_id = inv.id
LEFT JOIN (
    SELECT
        hist.isbn AS product_id,
        SUM(CASE WHEN TO_DAYS(NOW()) - TO_DAYS(hoje.datae) <= 0 THEN hist.quant ELSE 0 END) AS sales_last_0_days,
        SUM(CASE WHEN TO_DAYS(NOW()) - TO_DAYS(hoje.datae) <= 7 THEN hist.quant ELSE 0 END) AS sales_last_7_days,
        SUM(CASE WHEN TO_DAYS(NOW()) - TO_DAYS(hoje.datae) <= 30 THEN hist.quant ELSE 0 END) AS sales_last_30_days,
        SUM(CASE WHEN TO_DAYS(NOW()) - TO_DAYS(hoje.datae) <= 60 THEN hist.quant ELSE 0 END) AS sales_last_60_days,
        SUM(CASE WHEN TO_DAYS(NOW()) - TO_DAYS(hoje.datae) <= 90 THEN hist.quant ELSE 0 END) AS sales_last_90_days,
        SUM(CASE WHEN TO_DAYS(NOW()) - TO_DAYS(hoje.datae) <= 365 THEN hist.quant ELSE 0 END) AS sales_last_365_days,
        SUM(CASE WHEN YEAR(NOW()) = YEAR(hoje.datae) THEN hist.quant ELSE 0 END) AS sales_current_year,
        ROUND(SUM(CASE WHEN YEAR(NOW()) = YEAR(hoje.datae) THEN hist.quant * 365 ELSE 0 END) / DAYOFYEAR(NOW())) AS sales_forecast_year,
        SUM(CASE WHEN YEAR(NOW()) - 1 = YEAR(hoje.datae) THEN hist.quant ELSE 0 END) AS sales_year_2024,
        SUM(CASE WHEN YEAR(NOW()) - 2 = YEAR(hoje.datae) THEN hist.quant ELSE 0 END) AS sales_year_2023,
        SUM(CASE WHEN YEAR(NOW()) - 3 = YEAR(hoje.datae) THEN hist.quant ELSE 0 END) AS sales_year_2022,
        ROUND(SUM(CASE WHEN TO_DAYS(NOW()) - TO_DAYS(hoje.datae) <= 90 THEN hist.quant ELSE 0 END) / 3, 0) AS avg_sales_90_days,
        SUM(CASE WHEN YEAR(NOW()) = YEAR(hoje.datae) THEN hist.valor_base * hist.quant ELSE 0 END) /
        NULLIF(SUM(CASE WHEN YEAR(NOW()) = YEAR(hoje.datae) THEN hist.quant ELSE 0 END), 0) AS avg_sales_value_year
    FROM hist
    JOIN hoje ON hoje.id = hist.pedido
    WHERE hoje.nop IN (27, 28, 51, 76)
        AND hist.idcli <> 707602
        AND hist.valor_base > 0
    GROUP BY hist.isbn
) sales ON sales.product_id = inv.id
LEFT JOIN (
    SELECT
        n.isbn AS product_id,
        SUM(CASE WHEN ns.stage = 'shipping' AND n.quant > 0 AND n.state <> 9 AND MONTH(sh.status) = 0 THEN n.quant ELSE 0 END) AS in_transit,
        SUM(CASE WHEN n.quant > 0 AND n.state <> 9 AND MONTH(sh.status) = 0 THEN n.quant ELSE 0 END) AS total_next,
        SUM(CASE WHEN ns.stage = 'factory' AND n.quant > 0 AND n.state <> 9 AND MONTH(sh.status) = 0 THEN n.quant ELSE 0 END) AS scheduled,
        MIN(CASE WHEN MONTH(sh.status) > 0 THEN TO_DAYS(sh.status) + 1
                 WHEN MONTH(sh.date) > 0 THEN TO_DAYS(sh.date) + 45 END - TO_DAYS(NOW())) AS age_days,
        MIN(CASE WHEN MONTH(sh.arrival) > 0 THEN TO_DAYS(sh.arrival) + 15
                 WHEN MONTH(sh.date) > 0 THEN TO_DAYS(sh.date) + 45 END - TO_DAYS(NOW())) AS forecast_days
    FROM next n
    LEFT JOIN shipments sh ON sh.id = n.shipment
    LEFT JOIN next_stage ns ON ns.id = n.stage
    WHERE n.quant > 0 AND n.state <> 9
    GROUP BY n.isbn
) ship ON ship.product_id = inv.id
LEFT JOIN (
    SELECT
        hist.isbn AS product_id,
        MIN(TO_DAYS(NOW()) - TO_DAYS(hoje.data) + 1) AS last_sale_days
    FROM hist
    JOIN hoje ON hoje.id = hist.pedido
    WHERE hoje.nop IN (27, 28, 51, 76)
    GROUP BY hist.isbn
) last_sale ON last_sale.product_id = inv.id
LEFT JOIN Catalogo.packing pack ON pack.id = inv.embalagem
LEFT JOIN products_specifications prod_spec ON prod_spec.id = inv.id
LEFT JOIN product_quality quality ON quality.id = prod_spec.quality_id