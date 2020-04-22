# Chatbot Project:

This is a chatbot project made in Symfony and VueJS and that its main functionalities are:

- Show current balance to user
- Exchange money from a currency to another
- Create your account with the currency of preference
- Deposit and withdraw money

## Project Structure:
```
/
app/                #Symfony Rest API
infrastructure/     #Contains all the info about docker containers and scripts to run the project on docker
vue/                #VueJS SPA for chatbot interaction
docker-compose.yaml #The great docker recipe
README.md           #What you are reading right now
Vagrantfile         #Vagrant to run a virtual machine that excecutes docker inside (More info section Vagrant)
```

## To start:

First Clone the repository

This project use Vagrant + Docker to deploy the App. 
If you work in a Windows Environment this is the best option to use.
In this case you just need to setup your Windows credentials 
in the Vagrant file in order to sync you App folders. Install VirtualBox and 
Vagrant and using the command line interface execute:

Steps:
1. Run vagrant up  ```vagrant up```
2. Run vagrant ssh  ```vagrant ssh```
3. You'll have a docker ready linux environment and the App running dockerized
   in the IP: 192.168.33.10
4. Configure your apache virtual host [here] (infrastructure / containers / apache / config / vhost / symfony.conf).
5. In your local environment you need to add the virtual host domain like this: 
````
192.168.33.10 api.chatbotchallenge.local
```` 
6. Run sudo docker-compose up -d ```sudo docker-compose up -d```
7. Enter to app ```cd app```
8. Run composer install ```composer install```
9. Database configuration associated with docker mysql image is configured in the **.env** file
   ````
   DATABASE_URL=mysql://symfony:symfony@mysql:3306/symfony?serverVersion=5.7
   ````
   You can change it if you plan to use other database.
10.Run ```sudo docker-compose exec php php /var/www/app/bin/console doctrine:migrations:migrate``` 
    to migrate the database using the files inside  to  **src/migrations** folder
11. You don't need an initial database data, just register some new user using the chatbot commands or running
    ```sudo docker-compose exec php php /var/www/app/bin/console doctrine:fixtures:load``` to load the file data inside to
        **src/datafixtures**
        
#### For JWT Authentication configuration follow this steps:

Inside the symfony **app** folder generate the SSH keys, in this process you need to enter the passfrase specified in the .env file:

``` bash
$ mkdir -p config/jwt
$ openssl genrsa -out config/jwt/private.pem -aes256 4096
$ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

# Frontend Vuejs SPA for Chatbot Project

## Project setup
In order to install the project dependencies you need an update nodejs installation with npm installer tool.
After check the requirements just run:
```
npm install
```
### Configuration
- Edit .env file to configure API endpoint to match with the Project Rest API.

### Compiles and hot-reloads for development
```
npm run serve
```

### Compiles and minifies for production
```
npm run build
```

### Lints and fixes files
```
npm run lint
```

### Commands documentation

```
List all commands available
help

For register you should write the command:
register

For login you should write the command:
login

 How to exchange money:
 /exchange [FROM] [TO] AMOUNT 
 Example:
 /exchange USD EUR 10
 
 How to deposit money in your account:
 /deposit AMOUNT CURRENCY
 Example:
 /deposit 150 EUR
 
 How to withdraw money of your account:
 /withdraw AMOUNT CURRENCY
 Example:
 /withdraw 125 USD
 
 Check the balance of your account:
 /deposit CURRENCY
 Example:
 /balance USD
 
 How to change currency of your account:
 /currency CURRENCY
 Example:
 /currency USD
 After this action we'll exchange all your balance to the new currency using the current exchange rate
 
```

## Author

Reynaldo Zamora Pacheco
* https://github.com/gibarey45


