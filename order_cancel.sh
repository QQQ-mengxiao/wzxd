#!/bin/sh

PROCESS=`ps -ef|grep order_cancel_queue |grep -v grep|grep -v PPID|awk '{print $2}'`
echo $PROCESS
for i in $PROCESS
do
  kill -9 $i
done
nohup php /data/default/wzxd/order_cancel_queue.php & > /dev/order_cancel &
