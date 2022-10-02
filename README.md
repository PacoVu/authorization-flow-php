## How to build and run the demo

### Create a RingCentral app
* Create an application at https://developer.ringcentral.com.
* Select `Other Non-UI` option for the Platform type.
* Add the `ReadAccounts` and `ReadMessages` and `ReadCallLog` permissions for the app.
* Copy the Client id and Client secret and add them to the `./environment/.env-sandbox` file as shown in the next section.
* Generate a sandbox JWT token for the app. Copy the JWT token and add them to the `./environment/.env-sandbox` file as shown in the next section.

### Clone - Setup - Run the project
```
$ git clone https://github.com/paco-vu/authorization-flow-php

$ cd authorization-flow-php

$ curl -sS https://getcomposer.org/installer | php

$ php composer.phar install

$ cp environment/dotenv-sandbox environment/.env-sandbox
```

Specify the app credentials the .env-sandbox file accordingly
```
RC_SERVER_URL=https://platform.devtest.ringcentral.com
RC_REDIRECT_URL=http://localhost:5000/engine.php?oauth2callback

RC_CLIENT_ID=Your-Sandbox-App-Client-Id
RC_CLIENT_SECRET=Your-Sandbox-App-Client-Secret
```

### Run the demo
* Set `ENVIRONMENT=sandbox` in the `.env` file to run in the sandbox environment.

```
$ php -S localhost:5000
```

* Open your browser and enter the local address "locahost:5000"
* Login with a user in your sandbox account
* Click a link on the page to call an API
