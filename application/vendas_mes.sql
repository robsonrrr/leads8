CREATE ALGORITHM=UNDEFINED 
DEFINER=`robsonrr`@`%` 
SQL SECURITY DEFINER 
VIEW `Vendas_mes` AS 
SELECT 
    JSON_OBJECT(
        'id', v.id,
        'dolar', v.Dolar,
        'yuan', v.Yuan,
        'cash', v.Cash,
        'fix_plus', v.FixPlus,
        'fix_less', v.FixLess,
        'buildings', v.Buildings,
        'target_sets_machines_daily', v.TargetSetsMachinesDaily,
        'target_machines', v.Target_Machines,
        'target_bearings', v.Target_Bearings,
        'target_parts', v.Target_Parts,
        'target_auto', v.Target_Auto,
        'target_machines_customers', v.TargetMachinesCustomers,
        'target_machines_cidades', v.TargetMachinesCidades,
        'target_bearings_customers', v.TargetBearingsCustomers,
        'target_bearings_cidades', v.TargetBearingsCidades,
        'target_auto_cidades', v.TargetAutoCidades,
        'target_parts_cidades', v.TargetPartsCidades,
        'target_auto_customers', v.TargetAutoCustomers,
        'target_parts_customers', v.TargetPartsCustomers,
        'payable_exporter', v.PayableExporter,
        'pay_exporter_1', v.PayExporter1,
        'pay_exporter_2', v.PayExporter2,
        'pay_exporter_3', v.PayExporter3,
        'pay_exporter_4', v.PayExporter4,
        'pay_exporter_5', v.PayExporter5,
        'pay_exporter_6', v.PayExporter6,
        'pay_exporter_7', v.PayExporter7,
        'pay_exporter_8', v.PayExporter8,
        'pay_exporter_9', v.PayExporter9,
        'pay_exporter_10', v.PayExporter10,
        'pay_exporter_11', v.PayExporter11,
        'pay_exporter_12', v.PayExporter12,
        'workers_machines', v.Workers_Machines,
        'workers_bearings', v.Workers_Bearings,
        'workers_parts', v.Workers_Parts,
        'workers_auto', v.Workers_Auto,
        'paid_exporter', v.PaidExporter,
        'received_exporter', v.ReceivedExporter
    ) AS vars_json,
    c.id AS id,
    c.nome AS nome,
    c.cidade AS cidade,
    c.estado AS estado,
    h.data AS data,
    h.valor AS valor,
    SUM(hi.quant) AS sets,
    p.segmento_id AS segmento_id,
    p.segmento AS segmento,
    v.TargetSetsMachinesDaily AS target
FROM 
    hoje h
    LEFT JOIN clientes c ON c.id = h.idcli
    LEFT JOIN hist hi ON hi.pedido = h.id
    LEFT JOIN inv i ON i.id = hi.isbn
    LEFT JOIN produtos p ON p.id = i.idcf
    CROSS JOIN Vars v
WHERE 
    YEAR(h.data) = YEAR(CURDATE())
    AND MONTH(h.data) = MONTH(CURDATE())
    AND hi.valor_base > 0
    AND h.id > 1200000
    AND h.nop IN (27, 28, 51, 76)
GROUP BY 
    h.id, c.id, c.nome, c.cidade, c.estado, h.data, h.valor, p.segmento_id, p.segmento, v.TargetSetsMachinesDaily;
