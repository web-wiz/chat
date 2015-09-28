## Setup guide
	
1) Clone repo

2) On a terminal console navigate to project root run command **chmod -R 777 storage** (if you not a Windows user) 

3) Rename **.env.example** file to be **.env** and set your environment.

4) Find **config/database.php** file and set your database type (mysql or pgsql)

5) Make a database migrations and load seeds

6) On a terminal console navigate to project root and run command **php artisan chat:serve** to start the chat server (localhost on port 9090)
