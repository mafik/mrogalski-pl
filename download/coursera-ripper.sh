#!/bin/bash

# 1. Fill in the course name (from the course url)
COURSE="neuralnets-2012-001"

# 2. Copy cookies and place it in cookie.txt file (netscape format)
# You can use "cookie.txt export" extension to get it easily
COOKIES="cookie.txt"

# 3. Run this script

W_OPT="-q --content-disposition --trust-server-names --cookies=on\
	--load-cookies=$COOKIES --keep-session-cookies --save-cookies=$COOKIES\
	-P ./$COURSE/"

C_OPT="-sb $COOKIES"

INDEX_URL="https://class.coursera.org/$COURSE/lecture/index"

MP4_RE="https.\+download.mp4?lecture_id=\\w\+"
SRT_RE="https.\+/subtitles?q=\\w\+_en&format=srt"
PDF_RE="https.\+\.pdf"
PPT_RE="http.\+\.ppt\(x\)\?"

RE="\($MP4_RE\|$SRT_RE\|$PDF_RE\|$PPT_RE\)"

mkdir -p $COURSE

curl $C_OPT $INDEX_URL | grep -oe $RE | sort | uniq | shuf |\
	parallel --eta -j6 wget $W_OPT

# 4. Profit!
