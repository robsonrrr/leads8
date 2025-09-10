-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: vallery.catmgckfixum.sa-east-1.rds.amazonaws.com:3306
-- Tempo de geração: 13/08/2025 às 16:13
-- Versão do servidor: 5.7.44-log
-- Versão do PHP: 7.4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `mak`
--

-- --------------------------------------------------------

--
-- Estrutura para view `Vendas_Historia`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`robsonrr`@`%` SQL SECURITY DEFINER VIEW `Vendas_Historia`  AS  select `c`.`nome` AS `ClienteNome`,`c`.`cgc` AS `ClienteCGC`,`c`.`cnpj` AS `ClienteCNPJ`,`e`.`regiao` AS `RegiaoGeografica`,`e`.`uf` AS `EstadoSigla`,`m`.`NomeUF` AS `EstadoNome`,`e`.`codigo_ibge` AS `EstadoCodigoIBGE`,`m`.`Meso` AS `MesoRegiao`,`m`.`NomeMeso` AS `MesoRegiaoNome`,`m`.`Micro` AS `MicroRegiao`,`m`.`NomeMicro` AS `MicroRegiaoNome`,`m`.`Munic` AS `MunicipioCodigo`,`m`.`NomeMunic` AS `MunicipioNome`,`c`.`bairro` AS `Bairro`,`c`.`cep` AS `CEP`,`u`.`id` AS `VendedorID`,`h`.`idcli` AS `ClienteID`,`inv`.`id` AS `ProdutoISBN`,`p`.`segmento` AS `ProdutoSegmento`,`u`.`nick` AS `VendedorApelido`,`h`.`data` AS `DataVenda`,`hist`.`pedido` AS `PedidoID`,`hist`.`quant` AS `Quantidade`,`hist`.`valor_base` AS `ValorBase`,`inv`.`modelo` AS `ProdutoModelo`,`inv`.`marca` AS `ProdutoMarca`,`g`.`lat` AS `Latitude`,`g`.`lng` AS `Longitude` from ((((((((`hoje` `h` left join `clientes` `c` on((`c`.`id` = `h`.`idcli`))) left join `estados` `e` on((`e`.`uf` = `c`.`estado`))) left join `estatisticas`.`vTAB_MUNICIPIOS` `m` on(((`m`.`UF` = `e`.`codigo_ibge`) and (`m`.`NomeMunic` = `c`.`cidade`)))) left join `hist` on((`hist`.`pedido` = `h`.`id`))) left join `inv` on((`inv`.`id` = `hist`.`isbn`))) left join `produtos` `p` on((`p`.`id` = `inv`.`idcf`))) left join `rolemak_users` `u` on((`u`.`id` = `h`.`vendedor`))) left join `google_markers` `g` on((`g`.`idcli` = `c`.`id`))) where ((`h`.`id` > 1000000) and (`hist`.`valor_base` > 0) and (`h`.`nop` in (27,28,51,76))) order by `h`.`data` desc ;

--
-- VIEW `Vendas_Historia`
-- Dados: Nenhum
--

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
