#! /bin/sh
(/usr/bin/kill -9 `ps -ef | grep -v grep | grep yeleApi | awk '{print $2}'` & )  &&
(
cd /usr/local/yele-server/ &&

/usr/bin/git pull >> /usr/local/yele-server/git_crontab.log &&

/usr/bin/rm -rf webroot/yeleApi.pid &&

/usr/bin/php webroot/main.php start >> /usr/local/yele-server/git_crontab.log  &&

echo `date` >> /usr/local/yele-server/git_crontab.log
)
