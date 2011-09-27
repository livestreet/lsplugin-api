CREATE TABLE `prefix_api_session` (
	`key` char(32) NOT NULL,
	`uid` int(11) NOT NULL,
	PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;