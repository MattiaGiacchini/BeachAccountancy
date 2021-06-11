DROP DATABASE IF EXISTS BeachService;

CREATE DATABASE IF NOT EXISTS BeachService DEFAULT CHARACTER SET utf8;

USE BeachService;

-- Tables

CREATE TABLE BEACHSERVICE (
    idBS int not null auto_increment,
    numBS int not null,
    a tinyint(1),
    name varchar(150) not null,
    room int,
    umbrellas int not null,
    beds int not null,
    `check-in` date not null,
    `check-out` date not null,
    constraint ID primary key (idBS)
);


CREATE TABLE PERIOD (
    idPeriod int not null auto_increment,
    name varchar(50),
    `date-in` date not null,
    `date-out` date not null,
    price decimal(8,2),
    constraint ID primary key (idPeriod)
);


CREATE TABLE RENTINPERIOD (
    idRent int not null auto_increment,
    bs int not null,
    period int not null,
    days int not null,
    constraint ID primary key (idRent),
    constraint FKBS foreign key (bs) references BEACHSERVICE (idBS),
    constraint FKPERIOD foreign key (period) references PERIOD (idPeriod)
);
