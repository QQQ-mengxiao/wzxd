#!/bin/sh

PROCESS=`ps -ef|grep post_wx_queue|grep -v grep|grep -v PPID|awk '{print $2}'`
echo $PROCESS
for i in $PROCESS
do
  kill -9 $i
done
nohup php /data/default/wzxd/post_wx_queue.php & > /dev/post_wx &
