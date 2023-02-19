#
# Table structure for table 'tx_savlibrarymvc_domain_model_configuration'
#
CREATE TABLE tx_savlibrarymvc_domain_model_configuration (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) unsigned DEFAULT '0' NOT NULL,
    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
    deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
    hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);
#
# Table structure for table 'tx_savlibrarymvc_domain_model_export'
#
CREATE TABLE tx_savlibrarymvc_domain_model_export (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	fe_group int(11) unsigned DEFAULT '0' NOT NULL,
	name tinytext NOT NULL,
	cid int(11) DEFAULT '0' NOT NULL,
	template_file tinytext,
	variables text,
	xslt_file tinytext,
	exec tinytext,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);