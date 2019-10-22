#!/bin/bash
while  true; do
	php72 /var/www/crm.formetoo.ru/cron/system/email_sms.php > /dev/null 2>&1
	sleep 5
done