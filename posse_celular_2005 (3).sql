-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 27/11/2025 às 09:07
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `dac`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `posse_celular_2005`
--

CREATE TABLE `posse_celular_2005` (
  `id` int(11) NOT NULL,
  `sexo_e_estudo` varchar(100) NOT NULL,
  `regiao` varchar(50) NOT NULL,
  `pessoas_totais` bigint(20) DEFAULT NULL,
  `com_celular` bigint(20) DEFAULT NULL,
  `sem_celular` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `posse_celular_2005`
--

INSERT INTO `posse_celular_2005` (`id`, `sexo_e_estudo`, `regiao`, `pessoas_totais`, `com_celular`, `sem_celular`) VALUES
(1, 'TOTAL (Geral)', 'Brasil', 152740402, 56104605, 96602966),
(2, 'TOTAL (Geral)', 'Norte', 11420982, 3062123, 8358704),
(3, 'TOTAL (Geral)', 'Nordeste', 41212826, 9814838, 31397487),
(4, 'TOTAL (Geral)', 'Sudeste', 66575129, 27268599, 39274940),
(5, 'TOTAL (Geral)', 'Sul', 22784949, 10853578, 11930786),
(6, 'TOTAL (Geral)', 'Centro-Oeste', 10746516, 5105467, 5641049),
(7, 'Total - Sem instrução e - de 1 ano', 'Brasil', 16544614, 1402349, 15138777),
(8, 'Total - Sem instrução e - de 1 ano', 'Norte', 1378994, 104066, 1274928),
(9, 'Total - Sem instrução e - de 1 ano', 'Nordeste', 7838926, 399493, 7439433),
(10, 'Total - Sem instrução e - de 1 ano', 'Sudeste', 4817965, 468341, 4346136),
(11, 'Total - Sem instrução e - de 1 ano', 'Sul', 1490890, 243938, 1246952),
(12, 'Total - Sem instrução e - de 1 ano', 'Centro-Oeste', 1017839, 186511, 831328),
(13, 'Total - 1 a 3 anos', 'Brasil', 21491304, 3070386, 18416727),
(14, 'Total - 1 a 3 anos', 'Norte', 2011251, 187311, 1823785),
(15, 'Total - 1 a 3 anos', 'Nordeste', 7622991, 643292, 6979699),
(16, 'Total - 1 a 3 anos', 'Sudeste', 7657346, 1251723, 6401587),
(17, 'Total - 1 a 3 anos', 'Sul', 2799348, 638078, 2161270),
(18, 'Total - 1 a 3 anos', 'Centro-Oeste', 1400368, 349982, 1050386),
(19, 'Total - 4 a 7 anos', 'Brasil', 47646385, 12824073, 34807034),
(20, 'Total - 4 a 7 anos', 'Norte', 3651355, 670466, 2980889),
(21, 'Total - 4 a 7 anos', 'Nordeste', 12256522, 2214936, 10041586),
(22, 'Total - 4 a 7 anos', 'Sudeste', 20470358, 5756990, 14698090),
(23, 'Total - 4 a 7 anos', 'Sul', 7771267, 2856735, 4914532),
(24, 'Total - 4 a 7 anos', 'Centro-Oeste', 3496883, 1324946, 2171937),
(25, 'Total - 8 a 10 anos', 'Brasil', 25045868, 10908404, 14135339),
(26, 'Total - 8 a 10 anos', 'Norte', 1861092, 631357, 1229735),
(27, 'Total - 8 a 10 anos', 'Nordeste', 5485353, 1893084, 3592269),
(28, 'Total - 8 a 10 anos', 'Sudeste', 11881142, 5215497, 6663520),
(29, 'Total - 8 a 10 anos', 'Sul', 4020280, 2186225, 1834055),
(30, 'Total - 8 a 10 anos', 'Centro-Oeste', 1798001, 982241, 815760),
(31, 'Total - 11 a 14 anos', 'Brasil', 33025959, 20654435, 12367162),
(32, 'Total - 11 a 14 anos', 'Norte', 2131720, 1195197, 936523),
(33, 'Total - 11 a 14 anos', 'Nordeste', 6709877, 3672776, 3036600),
(34, 'Total - 11 a 14 anos', 'Sudeste', 16735674, 10486709, 6245689),
(35, 'Total - 11 a 14 anos', 'Sul', 5113772, 3634936, 1478251),
(36, 'Total - 11 a 14 anos', 'Centro-Oeste', 2334916, 1664817, 670099),
(37, 'Total - 15 anos ou mais', 'Brasil', 8576360, 7107115, 1467382),
(38, 'Total - 15 anos ou mais', 'Norte', 331549, 264016, 67533),
(39, 'Total - 15 anos ou mais', 'Nordeste', 1206768, 965919, 240849),
(40, 'Total - 15 anos ou mais', 'Sudeste', 4862420, 4037448, 823109),
(41, 'Total - 15 anos ou mais', 'Sul', 1495965, 1253354, 242611),
(42, 'Total - 15 anos ou mais', 'Centro-Oeste', 679658, 586378, 93280),
(43, 'HOMENS (Geral)', 'Brasil', 73794944, 28179132, 45600872),
(44, 'HOMENS (Geral)', 'Norte', 5642112, 1502733, 4139379),
(45, 'HOMENS (Geral)', 'Nordeste', 19993297, 4725110, 15267686),
(46, 'HOMENS (Geral)', 'Sudeste', 31925308, 13743780, 18167089),
(47, 'HOMENS (Geral)', 'Sul', 11004236, 5624862, 5379374),
(48, 'HOMENS (Geral)', 'Centro-Oeste', 5229991, 2582647, 2647344),
(49, 'Homens - Sem instrução e - de 1 ano', 'Brasil', 8003731, 779288, 7224443),
(50, 'Homens - 1 a 3 anos', 'Brasil', 11108056, 1747971, 9357775),
(51, 'Homens - 4 a 7 anos', 'Brasil', 23550876, 6825732, 16717918),
(52, 'Homens - 8 a 10 anos', 'Brasil', 12150437, 5606127, 6543153),
(53, 'Homens - 11 a 14 anos', 'Brasil', 15005707, 9907560, 5096005),
(54, 'Homens - 15 anos ou mais', 'Brasil', 3785511, 3246482, 538448),
(55, 'MULHERES (Geral)', 'Brasil', 78945458, 27925473, 51002094),
(56, 'MULHERES (Geral)', 'Norte', 5778870, 1559390, 4219325),
(57, 'MULHERES (Geral)', 'Nordeste', 21219529, 5089728, 16129801),
(58, 'MULHERES (Geral)', 'Sudeste', 34649821, 13524819, 21107851),
(59, 'MULHERES (Geral)', 'Sul', 11780713, 5228716, 6551412),
(60, 'MULHERES (Geral)', 'Centro-Oeste', 5516525, 2522820, 2993705),
(61, 'Mulheres - Sem instrução e - de 1 ano', 'Brasil', 8540883, 623061, 7914334),
(62, 'Mulheres - 1 a 3 anos', 'Brasil', 10383248, 1322415, 9058952),
(63, 'Mulheres - 4 a 7 anos', 'Brasil', 24095509, 5998341, 18089116),
(64, 'Mulheres - 8 a 10 anos', 'Brasil', 12895431, 5302277, 7592186),
(65, 'Mulheres - 11 a 14 anos', 'Brasil', 18020252, 10746875, 7271157),
(66, 'Mulheres - 15 anos ou mais', 'Brasil', 4790849, 3860633, 928934);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `posse_celular_2005`
--
ALTER TABLE `posse_celular_2005`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `posse_celular_2005`
--
ALTER TABLE `posse_celular_2005`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
