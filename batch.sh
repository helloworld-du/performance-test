#!/bin/bash
dir=`pwd`/`dirname $0`
fileNum=1
stime=`date +"%Y-%m-%d-%k-%M"`


if [ -z $1 ];then
	fileName=send.php
else
	fileName=$1
fi
runFile=${dir}/${fileName}

if [ ! -e ${runFile} ];then
	echo "No File: "${runFile}
	exit 1;
fi

#cleanFile=$dir/redis_test_clean.php
#php $cleanFile
if [ ! -d ${dir}/log/${fileName}/${stime} ];
then
   mkdir -p ${dir}/log/${fileName}/${stime}
fi

for l in $( seq 1 ${fileNum} )
do
	echo "Running "${l}
	nohup time php ${runFile} 2>&1 >> ${dir}/log/${fileName}/${stime}/${l}.log &
done
