CREATE TABLE USERS (
	ID INT(16) NOT NULL AUTO_INCREMENT,
	ENTRY_DATE TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	USERNAME VARCHAR(255) NOT NULL,
	PASSWORD VARCHAR(255) NOT NULL,

	PRIMARY KEY (ID)
);

CREATE TABLE FILES (
	ID INT(16) NOT NULL AUTO_INCREMENT,
	PUBLIC_ID VARCHAR(16) NOT NULL,
	OWNER_ID INT(16) NOT NULL,
	NAME VARCHAR(255) NOT NULL,
	FNAME VARCHAR(255) NOT NULL,
	ENTRY_DATE TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	TAGS TEXT,

	PRIMARY KEY (ID),
	FOREIGN KEY (OWNER_ID)
		REFERENCES USERS(ID),
	UNIQUE (PUBLIC_ID)
);
