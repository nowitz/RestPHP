create table calendar
(
	id int auto_increment	primary key,
	name varchar(128) not null comment 'Nazev kalendare, musi byt jedinecnej.',
	password varchar(128) not null comment 'Heslo ke kalendari.',
	password_edit varchar(128) not null comment 'Heslo pro editaci kalendare',
	background longtext not null comment 'Obrazek v base64 kodovani',
	template tinyint default '0' not null comment '0-nejedna se o sablonu / 1-jedna se o sablonu',
	constraint id	unique (id),
	constraint name	unique (name)
);

create index id_calendar	on calendar (id);
create index name_calendar on calendar (name);

create table flipper
(
	id int auto_increment	primary key,
	id_name varchar(128) null,
	front longtext null,
	text text null,
	back longtext null,
	date timestamp default CURRENT_TIMESTAMP not null,
	open tinyint(1) default '0' null,
	conditional tinyint(1) default '0' null,
	text_color varchar(64) default 'white' not null,
	border_color varchar(64) default 'white' not null,
	constraint id	unique (id),
	constraint flipper_calendar_name_fk
		foreign key (id_name) references calendar (name)
);

create index id_flipper	on flipper (id);
create index id_name_flipper on flipper (id_name);

create table user
(
	id int auto_increment	primary key,
	id_name varchar(128) not null comment 'odkaz na kalendar',
	email varchar(128) not null comment 'email',
	cost tinyint(1) not null comment '0-free / 1 - pracenej',
	cost_size int(10) not null comment 'vyse castky',
	constraint id	unique (id)
);

create index id_user	on user (id);
create index id_name_user	on user (id_name);

create table warning
(
	id int auto_increment	primary key,
	id_name varchar(128) not null,
	text text not null,
	constraint id	unique (id),
	constraint warning_calendar_fk
		foreign key (id_name) references calendar (name)
);

create index id_warning	on warning (id);
create index id_name_warning	on warning (id_name);

create table pays
(
	id int auto_increment	primary key,
	payment_order_id varchar(2048) null,
	merchant_order_number varchar(2048) null,
	payment_order_status_id varchar(2048) null,
	currency_id varchar(2048) null,
	amount varchar(2048) null,
	currency_base_units varchar(2048) null,
	payment_order_status_description varchar(2048) null,
	hash varchar(2048) null,
	validate varchar(1024) null,
	error varchar(1024) null,
	constraint id	unique (id)
);

create index id_pays	on pays (id);

