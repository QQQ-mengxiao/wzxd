#!/bin/sh

PROCESS=`ps -ef|grep ruku_cw_queue |grep -v grep|grep -v PPID|awk '{print $2}'`
echo $PROCESS
for i in $PROCESS
do
  kill -9 $i
done
nohup php /data/default/wzxd/ruku_cw_queue.php & > /dev/ruku_cw &
