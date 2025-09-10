

CREATE ALGORITHM=UNDEFINED DEFINER=`robsonrr`@`%` SQL SECURITY DEFINER VIEW `vw_leads_produtos` AS 

select `s`.`cSCart` AS `lead_id`,`s`.`dCart` AS `data_emissao_lead`,`s`.`dDelivery` AS `data_entrega`,
`s`.`cNatOp` AS `natureza_operacao_poid`,`s`.`cCustomer` AS `cliente_poid`,`s`.`cType` AS `lead_tipo`,`s`.
`cAuthorized` AS `lead_autorizado`,`s`.`cSource` AS `lead_fonte`,`s`.`cUser` AS `usuario_poid`,`s`.
`cSeller` AS `vendedor_poid`,`s`.`cCC` AS `cliente_do_cliente_poid`,`s`.`cPaymentType` AS `tipo_pagamento_poid`,
`s`.`vPaymentTerms` AS `prazo_pagamento_vezes`,`s`.`cPaymentTerms` AS `prazo_pagamento_novo`,`s`.
`cTransporter` AS `transportadora_poid`,`s`.`cEmitUnity` AS `unidade_emitente_poid`,`s`.`cLogUnity` AS `unidade_logistica_poid`,
`s`.`xRemarksFinance` AS `observacao_financeiro`,`s`.`xRemarksOBS` AS `observacao_interna`,`s`.`xRemarksLogistic` AS `observacao_logistica`,`s`.`xRemarksNFE` AS `observacao_nota_fiscal`,`s`.`cOrderWeb` AS `pedido_poid`,`s`.`xBuyer` AS `nome_comprador`,`s`.`cPurchaseOrder` AS `ordem_de_compra`,`s`.`vFreightType` AS `tipo_frete`,cast(`s`.`vFreight` as decimal(18,2)) AS `valor_frete`,`i`.`cCart` AS `produto_item_id`,`i`.`dInquiry` AS `data_emissao_produto`,`i`.`TTD` AS `produto_ttd`,`i`.`cProduct` AS `produto_poid`,cast(`i`.`qProduct` as decimal(18,4)) AS `produto_quantidade`,cast(`i`.`vProduct` as decimal(18,6)) AS `produto_valor`,cast(`i`.`vProductCC` as decimal(18,6)) AS `produto_valor_cliente_cliente`,`i`.`vStatus` AS `produto_status`,cast(`i`.`vProductOriginal` as decimal(18,6)) AS `produto_valor_original`,cast(`i`.`vIPI` as decimal(18,6)) AS `produto_ipi`,cast(`i`.`vDifal` as decimal(18,6)) AS `produto_difal`,`i`.`cSimilarCode` AS `produto_similar`,`i`.`tProduct` AS `produto_vezes`,cast((coalesce(`i`.`qProduct`,0) * coalesce(`i`.`vProduct`,0)) as decimal(18,6)) AS `valor_total_item`,(case coalesce(`i`.`vStatus`,-(1)) when 1 then 'Ativo' when 0 then 'Inativo' else 'Indefinido' end) AS `status_produto_desc`,
`c`.`nome` as cliente_nome,
`c`.`limite` as cliente_limite,
`c`.`cnpj` as cliente_cnpj,
`c`.`ender` as cliente_endereco,
`c`.`bairro` as cliente_bairro,
`c`.`cidade` as cliente_cidade,
`c`.`estado` as cliente_estado,
`c`.`cep` as cliente_cep,
`u`.`nick` as usuario_apelido,
`inv`.`nome` as produto_nome,
`inv`.`modelo` as produto_modelo,
`inv`.`peso` as produto_peso,
`inv`.`marca` as produto_marca,
`inv`.`embalagem` as produto_embalagem,
`produtos`.`segmento_id` as produto_segmento_id,
`produtos`.`segmento` as produto_segmento_nome
from (
    `sCart` `s` 
    left join `icart` `i` on((`i`.`cSCart` = `s`.`cSCart`))
    left join `clientes` `c` on((`c`.`id` = `s`.`cCustomer`))
    left join `users` `u` on((`u`.`id` = `s`.`cUser`))
    left join `inv` `inv` on((`inv`.`id` = `i`.`cProduct`))
    left join `produtos` `produtos` on((`produtos`.`id` = `inv`.`idcf`))
    )
WHERE YEAR(s.dCart) = YEAR(NOW()) AND MONTH(s.dCart) = MONTH(NOW()) AND DAY(s.dCart) = DAY(NOW())

ORDER BY `data_emissao_lead` DESC
