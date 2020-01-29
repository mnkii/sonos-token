# Sonos Token Generator

To control Sonos players using the Sonos API, the end user needs to authenticate with the Sonos OAuth system using a web browser. This is great
for devices with a web browser, but what if you want to experiment with your own Sonos system on a device that
does not have a browser such as your Arduino, ESP8266 or ESP32?

This PHP application is a simple Sonos integration to [authenticate with the Sonos login service](https://developer.sonos.com/build/direct-control/authorize/) and then provide you with the generated access and refresh tokens.
With these tokens you can then call the [Control API](https://developer.sonos.com/reference/control-api/) to control
players.

## Install this application

Sonos requires your authentication app to be HTTPS and publicly routable. These steps were created for an Ubuntu 18.04
server. You need domain or subdomain name pointing to your server and port 80 and 443 open to inbound traffic. Then run
the following:

```bash
sudo apt-get update
sudo apt-get install -y nginx php-fpm php-zip
sudo chown ubuntu /var/www
sudo chmod 755 -R /var/www

cd /var/www/
git clone https://github.com/mnkii/sonos-token.git

# Install example nginx config file
sudo cp /var/www/sonos-token/example-sonos-token.nginx.conf /etc/nginx/sites-enabled/sonos-token.nginx.conf

# Modify the example nginx config file to match your server - you probably want to at least change server_name
sudo vim /etc/nginx/sites-enabled/sonos-token.nginx.conf

# Get composer as per https://getcomposer.org/download/ then install app dependencies
./composer.phar install -d sonos-token

# Enable https by following the instructions on https://certbot.eff.org

# Restart nginx
sudo service nginx restart
```

## Create an integration on the Sonos developer site

1. Create an account or login to the [Sonos Developer Site](https://developer.sonos.com)
2. Choose "My Account" > "Integrations" > "New control integration"
3. Choose a name and description for your integration, click "Continue".
4. Choose a key name, click "Create key".
6. Click "Add redirect URI", enter "https://{your domain}/redirect". Click save.
7. "Event Callback URL" can be left blank
7. Copy the key and secret into the $config variable in public/index.php.

## Generate your access and refresh token

Visit the URL you are using to host this app and follow the flow to sign in to Sonos. You will then be shown your access
and refresh tokens along with an example curl command. Do not share these keys / curl commands, otherwise people are
able to control your Sonos system.

See the [API reference](https://developer.sonos.com/reference/) for the full guide to the Sonos API.
