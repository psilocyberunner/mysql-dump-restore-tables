# mysql-dump-restore-tables
Cut and restore single/several tables from huge mysqldump files


_**First - place your .sql mysqldump file into the same folder with app.php**_

Run the app:

``php app.php mysql:restore``

You'll have to answer a couple of questions about desired 
filename for restored data and usage of foreign key checks and sql 
mode in that file.

![image](https://user-images.githubusercontent.com/1381260/201945356-a3da85b3-03b1-4117-ae1a-d32814d9b9af.png)

After you can choose the file to read data from

![Untitled](https://user-images.githubusercontent.com/1381260/201947704-aae4aafe-a0f5-43ad-8110-44aa0974fc6d.png)

Application reads the structure of dump file and displays the results in ordered list

![Untitled](https://user-images.githubusercontent.com/1381260/201948450-b44cfb15-415c-4ce4-af4f-533de857db2c.png)


After you can type the desired table name(s) for restoration separated by comma
