CREATE TABLE `staffs` (
                          `id` int NOT NULL,
                          `name` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
                          `phone` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                          `login` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
                          `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
                          `hash` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
                          `role` tinyint NOT NULL DEFAULT '0',
                          `delete` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

INSERT INTO `staffs` VALUES (1,'Владимир','9164401342','admin','21232f297a57a5a743894a0e4a801fc3',NULL,1,NULL);