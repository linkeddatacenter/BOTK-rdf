#!/usr/bin/env bash

PROJECT="BOTK-rdf"
REPO="https://github.com/linkeddatacenter/BOTK-rdf.git" 
WEB_ROOT="/opt/$PROJECT/samples"

###########################################################################
# This scripts install all needed component in a fresh ubuntu 14.04 server
###########################################################################
if [ ! -f /tmp/install.lock ]; then
	apt-get update
	echo "apt-get update done" > /tmp/install.lock
	apt-get -y install git apache2 php5-common php5-curl libapache2-mod-php5 php5-cli curl phpunit

	curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

	# Clone  project in /opt (or use vagrant)
	if [ -d /vagrant ]; then
		ln -s /vagrant "/opt/$PROJECT"
	else 
		cd /opt; clone $REPO
	fi
	
	# Change apache 2 web document root 
	cat > /etc/apache2/sites-available/$PROJECT.apache.conf <<EOF
	<VirtualHost *:80>
		ServerAdmin webmaster@localhost
		DocumentRoot $WEB_ROOT
		ErrorLog ${APACHE_LOG_DIR}/error.log
		CustomLog ${APACHE_LOG_DIR}/access.log combined
	
	 <Directory $WEB_ROOT >
	    Options "FollowSymLinks"
	    AllowOverride All
	    Require all granted
	    Order allow,deny
	    Allow from all
	  </Directory>
	</VirtualHost>
EOF
	a2dissite 000-default.conf
	a2ensite "$PROJECT.apache.conf"
	service apache2 restart

fi

if [ ! -f "/opt/$PROJECT/composer.lock" ]; then 
	cd "/opt/$PROJECT"; composer install
fi