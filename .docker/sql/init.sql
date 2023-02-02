DROP DATABASE IF EXISTS betoola;
CREATE DATABASE IF NOT EXISTS betoola;
grant usage on *.* to 'betoola'@'localhost' IDENTIFIED BY 'Hour8aebEighoh9oGaz7che2';
grant all privileges on betoola.* to 'betoola'@'localhost';
grant usage on *.* to 'betoola'@'%' IDENTIFIED BY 'Hour8aebEighoh9oGaz7che2';
grant all privileges on betoola.* to 'betoola'@'%';
