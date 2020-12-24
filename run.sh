#!/bin/bash

RED='\033[0;31m'
GRN='\033[0;32m'
ORN='\033[0;33m'
echo -e "${ORN}  ___          _             _____  ____        _____ TM"
echo -e "${ORN} |_ _| _ __   (_)  ___   ___|_   _|| __ )   ___|_   _|"
echo -e "${ORN}  | | | '_ \  | | / _ \ / __| | |  |  _ \  / _ \ | |"
echo -e "${ORN}  | | | | | | | ||  __/| (__  | |  | |_) || (_) || |"
echo -e "${ORN} |___||_| |_|_/ | \___| \___| |_|  |____/  \___/ |_|"
echo -e "${ORN}             |__/                           @TariqHawis"
echo
if [ -e '/usr/bin/php' ]
then
   CURL=$(php -m | grep -i curl)
   if [ $CURL == 'curl' ]
   then
      echo -e "${GRN}InjectBot Launched ..."
      echo -e "${GRN}Usage:  Open at your browser: http://127.0.0.1:11111"
      php -S 127.0.0.1:11111 &> /dev/null
   else
      echo -e "${RED}php-curl is required.."
   fi
else
   echo -e "${RED}php is required.."
fi
exit 0
