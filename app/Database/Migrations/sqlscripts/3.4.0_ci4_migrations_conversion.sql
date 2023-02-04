ALTER TABLE `ospos_migrations`
	MODIFY COLUMN `version` VARCHAR(255) NOT NULL,
	ADD `id` bigint(20) UNSIGNED NOT NULL,
	ADD `class` varchar(255) NOT NULL,
	ADD `group` varchar(255) NOT NULL,
	ADD `namespace` varchar(255) NOT NULL,
	ADD `time` int(11) NOT NULL,
	ADD `batch` int(11) UNSIGNED NOT NULL;

ALTER TABLE `ospos_migrations`
	ADD PRIMARY KEY (`id`);

ALTER TABLE `ospos_migrations`
	MODIFY COLUMN `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

UPDATE `ospos_migrations`
SET
	`namespace` = 'App',
	`time` = 0,
	`batch` = 0
WHERE `ospos_migrations`.`id` = 1




SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Table structure for table `ospos_migrations`
--

CREATE TABLE `ospos_migrations` (
	`id` bigint(20) UNSIGNED NOT NULL,
	`version` varchar(255) NOT NULL,
	`class` varchar(255) NOT NULL,
	`group` varchar(255) NOT NULL,
	`namespace` varchar(255) NOT NULL,
	`time` int(11) NOT NULL,
	`batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `ospos_migrations`
--
# TODO: This needs to be programmatically done in PHP to bring an existing database to the same version it was on before CI4 so no migrations are re-run.
INSERT INTO `ospos_migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
	(1, '20170501150000', 'App\\Database\\Migrations\\Migration_Upgrade_To_3_1_1', 'development', 'App', 1675515838, 1),
	(2, '20170502221506', 'App\\Database\\Migrations\\Migration_Sales_Tax_Data', 'development', 'App', 1675515838, 1),
	(3, '20180225100000', 'App\\Database\\Migrations\\Migration_Upgrade_To_3_2_0', 'development', 'App', 1675515838, 1),
	(4, '20180501100000', 'App\\Database\\Migrations\\Migration_Upgrade_To_3_2_1', 'development', 'App', 1675515838, 1),
	(5, '20181015100000', 'App\\Database\\Migrations\\Migration_Attributes', 'development', 'App', 1675515838, 1),
	(6, '20190111270000', 'App\\Database\\Migrations\\Migration_Upgrade_To_3_3_0', 'development', 'App', 1675515838, 1),
	(7, '20190129212600', 'App\\Database\\Migrations\\Migration_IndiaGST', 'development', 'App', 1675515838, 1),
	(8, '20190213210000', 'App\\Database\\Migrations\\Migration_IndiaGST1', 'development', 'App', 1675515838, 1),
	(9, '20190220210000', 'App\\Database\\Migrations\\Migration_IndiaGST2', 'development', 'App', 1675515838, 1),
	(10, '20190301124900', 'App\\Database\\Migrations\\Migration_decimal_attribute_type', 'development', 'App', 1675515838, 1),
	(11, '20190317102600', 'App\\Database\\Migrations\\Migration_add_iso_4217', 'development', 'App', 1675515838, 1),
	(12, '20190427100000', 'App\\Database\\Migrations\\Migration_PaymentTracking', 'development', 'App', 1675515839, 1),
	(13, '20190502100000', 'App\\Database\\Migrations\\Migration_RefundTracking', 'development', 'App', 1675515839, 1),
	(14, '20190612100000', 'App\\Database\\Migrations\\Migration_DBFix', 'development', 'App', 1675515839, 1),
	(15, '20190615100000', 'App\\Database\\Migrations\\Migration_fix_attribute_datetime', 'development', 'App', 1675515839, 1),
	(16, '20190712150200', 'App\\Database\\Migrations\\Migration_fix_empty_reports', 'development', 'App', 1675515839, 1),
	(17, '20191008100000', 'App\\Database\\Migrations\\Migration_receipttaxindicator', 'development', 'App', 1675515839, 1),
	(18, '20191231100000', 'App\\Database\\Migrations\\Migration_PaymentDateFix', 'development', 'App', 1675515839, 1),
	(19, '20200125100000', 'App\\Database\\Migrations\\Migration_SalesChangePrice', 'development', 'App', 1675515839, 1),
	(20, '20200202000000', 'App\\Database\\Migrations\\Migration_TaxAmount', 'development', 'App', 1675515839, 1),
	(21, '20200215100000', 'App\\Database\\Migrations\\Migration_taxgroupconstraint', 'development', 'App', 1675515839, 1),
	(22, '20200508000000', 'App\\Database\\Migrations\\Migration_image_upload_defaults', 'development', 'App', 1675515839, 1),
	(23, '20200819000000', 'App\\Database\\Migrations\\Migration_modify_attr_links_constraint', 'development', 'App', 1675515839, 1),
	(24, '20201108100000', 'App\\Database\\Migrations\\Migration_cashrounding', 'development', 'App', 1675515839, 1),
	(25, '20201110000000', 'App\\Database\\Migrations\\Migration_add_item_kit_number', 'development', 'App', 1675515839, 1),
	(26, '20210103000000', 'App\\Database\\Migrations\\Migration_modify_session_datatype', 'development', 'App', 1675515839, 1),
	(27, '20210422000000', 'App\\Database\\Migrations\\Migration_database_optimizations', 'development', 'App', 1675515841, 1),
	(28, '20210422000001', 'App\\Database\\Migrations\\Migration_remove_duplicate_links', 'development', 'App', 1675515841, 1),
	(29, '20210714140000', 'App\\Database\\Migrations\\Migration_move_expenses_categories', 'development', 'App', 1675515841, 1),
	(30, '20220127000000', 'App\\Database\\Migrations\\Convert_to_ci4', 'development', 'App', 1675515841, 1);

--
-- Indexes for table `ospos_migrations`
--
ALTER TABLE `ospos_migrations`
	ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `ospos_migrations`
--
ALTER TABLE `ospos_migrations`
	MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
COMMIT;
