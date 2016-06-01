SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `testdb`
--

-- simple sort
CREATE TABLE `model` (
    `id`   INT  NOT NULL AUTO_INCREMENT,
    `sort` INT  NOT NULL DEFAULT 0,
    `name` TEXT NOT NULL,

    PRIMARY KEY (`id`),
    INDEX `sort`(`sort`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_general_ci;

INSERT INTO `model` (`id`, `sort`, `name`) VALUES
    (1, 0, 'Name 1'),
    (2, 1, 'Name 2'),
    (3, 2, 'Name 3'),
    (4, 3, 'Name 4'),
    (5, 4, 'Name 5'),
    (6, 5, 'Name 6'),
    (7, 6, 'Name 7'),
    (8, 7, 'Name 8'),
    (9, 8, 'Name 9'),
    (10, 9, 'Name 10'),
    (11, 10, 'Name 11'),
    (12, 11, 'Name 12'),
    (13, 12, 'Name 13'),
    (14, 13, 'Name 14'),
    (15, 14, 'Name 15');

-- sort with one field condition
CREATE TABLE `model2` (
    `id`        INT  NOT NULL AUTO_INCREMENT,
    `condition` INT  NOT NULL DEFAULT 0,
    `sort`      INT  NOT NULL DEFAULT 0,
    `name`      TEXT NOT NULL,

    PRIMARY KEY (`id`),
    INDEX `sort`(`sort`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_general_ci;

INSERT INTO `model2` (`id`, `condition`, `sort`, `name`) VALUES
    (1, 1, 0, 'Name 1'),
    (2, 1, 1, 'Name 2'),
    (3, 1, 2, 'Name 3'),
    (4, 1, 3, 'Name 4'),
    (5, 1, 4, 'Name 5'),
    (6, 2, 0, 'Name 6'),
    (7, 2, 1, 'Name 7'),
    (8, 2, 2, 'Name 8'),
    (9, 2, 3, 'Name 9'),
    (10, 2, 4, 'Name 10'),
    (11, 2, 5, 'Name 11'),
    (12, 2, 6, 'Name 12'),
    (13, 2, 7, 'Name 13'),
    (14, 2, 8, 'Name 14'),
    (15, 3, 0, 'Name 15');

-- sort with multi fields condition
CREATE TABLE `model3` (
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `condition`  INT          NOT NULL DEFAULT 0,
    `condition2` VARCHAR(200) NOT NULL DEFAULT '',
    `sort`       INT          NOT NULL DEFAULT 0,
    `name`       TEXT         NOT NULL,

    PRIMARY KEY (`id`),
    INDEX `sort`(`sort`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_general_ci;

INSERT INTO `model3` (`id`, `condition`, `condition2`, `sort`, `name`) VALUES
    (1, 1, 'a', 0, 'Name 1'),
    (2, 1, 'a', 1, 'Name 2'),
    (3, 1, 'a', 2, 'Name 3'),
    (4, 1, 'b', 0, 'Name 4'),
    (5, 1, 'b', 1, 'Name 5'),
    (6, 2, 'a', 0, 'Name 6'),
    (7, 2, 'a', 1, 'Name 7'),
    (8, 2, 'a', 2, 'Name 8'),
    (9, 2, 'a', 3, 'Name 9'),
    (10, 2, 'c', 0, 'Name 10'),
    (11, 2, 'c', 1, 'Name 11'),
    (12, 2, 'c', 2, 'Name 12'),
    (13, 2, 'c', 3, 'Name 13'),
    (14, 2, 'c', 4, 'Name 14'),
    (15, 3, 'a', 0, 'Name 15');

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
