## Setup guide
	
1) Clone repo

2) Standing on repo folder, run "composer install" and then "composer update --no-scripts" from a terminal.

// 5) Add a virtual host with ServerName "chat.dev" (or use whatever server name you like). This steps has more steps within, so please search on google how to add a virtual host.

6) On a terminal console navigate to project root run command "chmod -R 777 storage" 

7) Rename .env.example file (located at root structure) to be .env only and update your database credentials. (Note that a database server is needed to run this app)

8) On a terminal console navigate to project root run command "php artisan migrate:install"

9) On a terminal console navigate to project root run command "php artisan migrate"

10) On a terminal console navigate to project root run command "php artisan chat:serve" to start the chat server (localhost on port 9090)

11) Whohaa! You'r done! Open browser and enter url http://chat.dev, login and happy chatting!
