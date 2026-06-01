tail -f /var/log/nginx/error.log
vi /etc/nginx/nginx.conf 
nginx -t
systemctl restart nginx
vi /etc/nginx/nginx.conf 
vi /etc/nginx/sites-enabled/evoegitim.com.conf 
nginx -t
systemctl restart nginx
vi /etc/nginx/sites-enabled/evoegitim.com.conf 
systemctl restart nginx
systemctl restart php7.4-fpm
vi /etc/nginx/sites-enabled/evoegitim.com.conf 
vi /etc/nginx/nginx.conf 
exit
date
sudo timedatectl set-timezone Europe/Istanbul
date
vi /etc/php/7.4/fpm/php.ini 
systemctl restart nginx
systemctl restart php7.4-fpm
mysql
date
systemctl restart mysql
mysql
apt update && apt upgrade -y
cd /var/www/evoegitim/public/
ls -la
mkdir backup
mv config/ backup/
mv includes/ backup/
mv management/ backup/
mv new-site/ backup/
mv pay/ backup/
mv rooms/ backup/
mv student/ backup/
mv teacher/ backup/
mv uploads/ backup/
mv vbs/ backup/
mv vendor/ backup/
mv backup/ ../
ls -la
mv .htaccess ../backup/
mv .well-known/ ../backup/
ls -la
rm -rf *
ls -la
cd ..
ls -la
cd backup/
mv * ../backup/
ls -la
mv -R * ../public/
mv -r * ../public/
ls -la
mv * ../public/
ls -la
mv .htaccess ../public/
mv .well-known/ ../public/
ls -la
cd ..
ls -la
rm -rf backup/
cd public/
ls -la
cd new-site/
ls -la
mv * ../
ls -la
mv .htaccess ../
mv .gitignore ../
mv includes/ ../
ls -la
cd includes/
ls -la
cd ..
ls -la
mv * ../
cd ..
ls -la
cd includes/
ls -la
cd ..
mv includes/ includes_bak
cd new-site/
mv includes/ ../
ls -la
cd ..
ls -la
cd ..
ls -la
chown -R www-data.www-data public/
cd /etc/nginx/
ls -la
cd sites-available/
ls -la
vi evoegitim.com.conf 
systemctl restart nginx
systemctl restart php7.4-fpm
tail -f /var/log/nginx/error.log
cd /var/www/evoegitim/public/
ls -la
tail -f /var/log/nginx/error.log
cd /var/www/evoegitim/
ls -la
cd public/
ls -la
zip -r backup.zip *
apt install zip
zip -r backup.zip *
mv backup.zip ../
ls -la
cd ..
ls -la
chown -R www-data.www-data public/
shutdown -r now
apt update && apt upgrade
ls -la
cd ..
ls -la
systemctl enable nginx
systemctl enable mysql
systemctl enable php7.4-fpm
ls -la
cd /var/www/evoegitim/
ls -la
mv backup.zip public/
chown -R www-data.www-data public/
cd public/
ls -la
rm -rf backup.zip 
cd /var/www/evoegitim/
ls -la
chown -R www-data.www-data site2.zip 
mv site2.zip public/
cd public/
ls -la
rm -rf site2.zip 
ls -la
cd ..
ls -la
mkdir db
mv evoegiti_evo.sql db/
ls -la
zip -r db2.zip db/
mv db2.zip public/
cd public/
ls -la
rm -rf db2.zip 
cd ..
ls -la
rm -rf evoegiti_tech.sql 
rm -rf db/
ls -la
cd ..
ls -la
cd ..
ls -la
cd /etc/nginx/
ls -la
cd sites-available/
ls -la
vi evoegitim.com.conf 
ls -la
cd /var/www/evoegitim/
ls -la
cd public/
ls -la
exit
systemctl restart nginx
cd /etc/nginx/
ls -la
cd cache/
ls -la
cd ..
cd /var/www/evoegitim/
ls -la
cd public/
ls -la
systemctl restart nginx
systemctl restart php7.4-fpm
exit
ls -la
cd /var/www/html/
ls -la
cd ..
cd evoegitim/
ls -la
cd public/
ls -la
cd ..
ls -la
zip -r site2.zip public/
mysql
mysqldump evoegiti_evo > evoegiti_evo.sql
mysqldump evoegiti_tech > evoegiti_tech.sql
ls
ls sna
ls snap/
dir
cd..
cd ..
ls
cd home
ls
cd web
ls
cd ..
ls
cd mnt
ls
cd ..
ls
zip -r ~/home_backup.zip /home
cd home
ls
cd ..
ls
cd root
ls
sudo
sudo less /var/log/auth.log
sudo ls 
sudo nano /etc/ssh/sshd_config
sudo systemctl restart sshd
ls -la /root/.ssh/
sudo journalctl -u sshd -n 30
ssh -vvv root@207.154.222.79
cat /etc/hosts.deny
sudo fail2ban-client status sshd
sudo ufw status verbose
cat /root/.ssh/authorized_keys
$ curl -v --upload-file ./hello.txt https://transfer.sh/hello.txt
curl -v --upload-file ./hello.txt https://transfer.sh/hello.txt
curl -v --upload-file ./home_backup.zip https://transfer.sh/h.zip
sudo ls
cd..
cd ..
ls
cd mnt
ls
cd ..
cd dev
ls
sudo systemctl status apache2
sudo systemctl status nginx
cd ..
ls
cd var
ls
cd www
ls
cd evoegitim/
ls
cd public/
ls
cd ..
sudo tar -czvf /var/www/evoegitim/public/evoegitim-backup-2025-10-31.tar.gz /var/www/evoegitim
s
l
ls
cd public/
ls
sudo mysql -u root -p
sudo mysqldump -u root -p evoegiti_evo > /var/www/evoegitim/public/evoegitim-db-backup-2025-10-31.sql
ls
sudo mysqldump -u root -p evoegiti_tech > /var/www/evoegitim/public/evoegitim-db-backup-2025-10-31.sql
sudo chown www-data:www-data /var/www/evoegitim/public/evoegitim-db-backup-2025-10-31.sql
sudo chmod 644 /var/www/evoegitim/public/evoegitim-db-backup-2025-10-31.sql
sudo gzip /var/www/evoegitim/public/evoegitim-db-backup-2025-10-31.sql
ls
sudo mysqldump -u root -p evoegiti_tech > /var/www/evoegitim/public/evoegitim-db-backup-2025-10-311.sql
sudo mysqldump -u root -p --single-transaction --skip-lock-tables --no-tablespaces evoegiti_tech > /var/www/evoegitim/public/evoegitim-db-backup-2025-120-31.sql
rm evoegitim-db-backup-2025-10-31.sql.gz 
ls
rm evoegitim-backup-2025-10-31.tar.gz 
sudo gzip /var/www/evoegitim/public/evoegitim-db-backup-2025-120-31.sql
ls
rm evoegitim-db-backup-2025-120-31.sql.gz 
ls
em evoegitim-db-backup-2025-10-311.sql 
rm evoegitim-db-backup-2025-10-311.sql 
ls
cd /var/www
ls
cd evoegitim/
ls
cd public/
ls
cd student/
wget https://rebinweb.com/lesson-create.abd
ls
mv lesson-create.php old-lesson-create.php
ls
mv lesson-create.abd lesson-create.php
ls
cd ..
ls
cd var
ls
cd www
ls
cd evoegitim/
ls
cd public/
ls
cd student/
ls
cat lesson-create.php 
opcache_reset();
sudo systemctl reload php8.2-fpm
sudo systemctl reload php7.4-fpm.service 
sudo nano meta.php
ls
cd ..
ls
cd var
ls
cd www
ls
eval 
ls
cd evoegitim
ls
cd public
cd includes
ls
sudo nano meta.php
s
sudo nano meta.php
ls
sudo nano header.php
ls
cd ..
cd var/www/evoegitim/inclues
cd var/www/evoegitim/includes
cd var/www/evoegitim/public/
cd includes
ls
sudo nano header.php
ls
cs ..
cd ..
ls
cd var/www/evoegitim/public/includes
ls
sudo nano header.php
sudo nano footer.php 
sudo nano header.php
ls
cd ..
cd var/www/evoegitim/public/teacher/
sudo nano lesson-calendar.php 
ls
cd ..
مس
ls
cd var/www/evoegitim/public/teacher/
cd calendar/
sudo nano read-lesson.php 
cd ..
ls
cd ..
cd var/
cd www
ls
cd evoegitim/
cd public/
cd config
nano solo-times.php
cd ..
ls
cd teacher/
ls
nano appointment.php 
nano lesson-create.php 
wget https://rebinweb.com/lesson-create.php
ls
mv lesson-create.php lesson-createbackup.php
mv lesson-create.php.1 lesson-create.php
ls
cd ..
ls
cd config
nano solo-times.php 
cd ..
cd teacher/
mv lesson-create.php lesson-create1.php
get https://rebinweb.com/lesson-create.php
wget https://rebinweb.com/lesson-create.php
ls
mv appointment.php appointmentback.php 
wget https://rebinweb.com/appintment.php
wget https://rebinweb.com/appointment.php
ls
wget https://rebinweb.com/appointment.php
wget https://rebinweb.com/appointment.php1
mv appointment.php1 appointment.php
ls
cd ..
cd var/www/evoegitim/public/teacher/student/
ls
mv delay-lesson.php delay-lesson.php2 
get https://rebinweb.com/delay-lesson.php1
wget https://rebinweb.com/delay-lesson.php1
mv delay-lesson.php1 delay-lesson.php
ls
cd ..
mv available-calendar.php available-calendar.php2 
wget https://rebinweb.com/available-calendar.php
ls
nano available-calendar.php
sudo nano available-calendar.php
wget https://rebinweb.com/available-calendar.php1
rm available-calendar.php
mv available-calendar.php1 available-calendar.php
cd ..
cd config/
sudo nano solo-times.php 
ls
cd ..
cd var/www/evoegitim/public/
ls
crontab -e
crontab -l
cat /var/log/cron_lessons.log
php -v
ls -l /usr/bin/php*
/usr/bin/php7.4 /var/www/evoegitim/public/update_lesson_statuses.php
crontab -e
ps aux --sort=-%cpu | head -20
sudo ufw deny from 136.114.65.126
sudo ufw deny 3306
sudo systemctl restart mysql
sudo systemctl restart php7.4-fpm
sudo systemctl restart nginx
