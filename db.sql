CREATE TABLE `links` (
  `Id` varchar(50) PRIMARY KEY NOT NULL,
  `Owner` varchar(50) NOT NULL,
  `Password` varchar(50) NOT NULL,
  `Extension` varchar(50) NOT NULL,
  `ContentType` varchar(50) NOT NULL,
  `NextExpiry` varchar(50) NOT NULL,
  `Expires` datetime NOT NULL
);