<?php
// Multi-server configuration for LocalDevServer phpMyAdmin
$i = 0;

// Server 1: MySQL 8.x
$i++;
$cfg['Servers'][$i]['auth_type'] = 'cookie';
$cfg['Servers'][$i]['host'] = 'mysql';
$cfg['Servers'][$i]['port'] = '3306';
$cfg['Servers'][$i]['verbose'] = 'MySQL 8.x Server';
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['AllowNoPassword'] = false;

// Server 2: MariaDB
$i++;
$cfg['Servers'][$i]['auth_type'] = 'cookie';
$cfg['Servers'][$i]['host'] = 'mariadb';
$cfg['Servers'][$i]['port'] = '3306';
$cfg['Servers'][$i]['verbose'] = 'MariaDB Server';
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['AllowNoPassword'] = false;

// Max rows and timeout
$cfg['MaxRows'] = 100;
$cfg['LoginCookieValidity'] = 86400;

