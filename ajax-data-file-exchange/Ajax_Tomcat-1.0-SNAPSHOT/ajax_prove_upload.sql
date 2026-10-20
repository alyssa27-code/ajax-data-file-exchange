drop database if exists ajax_prove_upload;
create database ajax_prove_upload;
use ajax_prove_upload;
drop table if exists ajax_utenti;
create table ajax_utenti(
    username varchar(30) not null,
    nome varchar(260) not null,
    cognome varchar(260) not null,
    PRIMARY KEY(username)
)Engine = InnoDB;
drop table if exists ajax_file_utenti;
create table ajax_file_utenti(
    id int AUTO_INCREMENT not null,
    id_utente varchar(30) not null,
    percorso varchar(260) not null unique,
    PRIMARY KEY(id),
    FOREIGN KEY (id_utente) REFERENCES ajax_utenti(username)
)Engine = InnoDB;

    