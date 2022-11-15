# mysql-dump-restore-tables
Cut and restore single/several tables from huge mysqldump files


_**First - place your .sql mysqldump file into the same folder with app.php**_

Run the app:

``php app.php mysql:restore``

You'll have to answer a couple of questions about desired 
filename for restored data and usage of foreign key checks and sql 
mode in that file.


