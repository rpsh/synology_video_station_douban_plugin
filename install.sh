#!/bin/bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH

clear;

# VAR   ******************************************************************
vVersion='v202003071711';
vPlugins='/var/packages/VideoStation/target/plugins';
vUI='/var/packages/VideoStation/target';
vAction=$1;
pack='https://github.com/rpsh/synology_video_station_douban_plugin/archive/v1.3.tar.gz'
dist='synology_video_station_douban_plugin-1.3'
# Logo  ******************************************************************
CopyrightLogo="
                        DS Video 豆瓣刮削器 $vVersion
                        rpsh 基于 atroy、jswh 的代码修改

==========================================================================";
echo "$CopyrightLogo";

# Function List *******************************************************************************
function install()
{
    cd /tmp/;

    # backup
    if [ ! -f "$vPlugins/syno_themoviedb/search.php.orig" ]; then
        mv $vPlugins/syno_themoviedb/search.php $vPlugins/syno_themoviedb/search.php.orig
    fi;
    if [ ! -f "$vPlugins/syno_synovideodb/search.php.orig" ]; then
        mv $vPlugins/syno_synovideodb/search.php $vPlugins/syno_synovideodb/search.php.orig
    fi;
    if [ ! -f "$vPlugins/syno_thetvdb/search.php.orig" ]; then
        mv $vPlugins/syno_thetvdb/search.php $vPlugins/syno_thetvdb/search.php.orig
    fi;
    if [ ! -f "$vUI/ui/videostation2.js.orig" ]; then
        mv $vUI/ui/videostation2.js $vUI/ui/videostation2.js.orig
    fi;
    if [ ! -f "$vPlugins/syno_file_assets/episode.inc.php.orig" ]; then
        mv $vPlugins/syno_file_assets/episode.inc.php $vPlugins/syno_file_assets/episode.inc.php.orig
    fi;

    wget --no-check-certificate $pack -O syno_douban.tar.gz;
    tar -zxvf syno_douban.tar.gz

        cd $dist

    \cp -rfa ./syno_themoviedb $vPlugins/;
    \cp -rfa ./syno_synovideodb $vPlugins/;
    \cp -rfa ./syno_thetvdb $vPlugins/;
    \cp -rfa ./syno_file_assets $vPlugins/;
    \cp -rfa ./ui $vUI/;

    chmod 0755 $vPlugins/syno_themoviedb/search.php $vPlugins/syno_synovideodb/search.php $vPlugins/syno_thetvdb/search.php $vUI/ui/videostation2.js $vPlugins/syno_file_assets/episode.inc.php

    chown VideoStation:VideoStation $vPlugins/syno_themoviedb/search.php $vPlugins/syno_synovideodb/search.php $vPlugins/syno_thetvdb/search.php $vUI/ui/videostation2.js $vPlugins/syno_file_assets/episode.inc.php

    cd -

    echo '==========================================================================';
    echo "恭喜, DS Video 豆瓣刮削器 $vVersion 安装/更新成功.";
    echo '==========================================================================';
}

function uninstall()
{
    mv -f $vPlugins/syno_themoviedb/search.php.orig $vPlugins/syno_themoviedb/search.php
    mv -f $vPlugins/syno_synovideodb/search.php.orig $vPlugins/syno_synovideodb/search.php
    mv -f $vPlugins/syno_thetvdb/search.php.orig /$vPlugins/syno_thetvdb/search.php
    mv -f $vPlugins/syno_file_assets/episode.inc.php.orig /$vPlugins/syno_file_assets/episode.inc.php
    mv -f $vUI/ui/videostation2.js.orig $vUI/ui/videostation2.js

    rm $vPlugins/syno_themoviedb/douban.php

    rm -rf /tmp/$dist;

    echo '恭喜, DS Video 豆瓣刮削器已卸载.';
    echo '==========================================================================';
}

# SHELL     ******************************************************************
if [ "$vAction" == 'install' ]; then
    if [ ! -f "$vPlugins/syno_themoviedb/search.php.orig" ]; then
        install;
    else
        echo '注意！你已经安装豆瓣刮削器.';
        echo '==========================================================================';
        exit 1;
    fi;
elif [ "$vAction" == 'uninstall' ]; then
    if [ ! -f "$vPlugins/syno_themoviedb/search.php.orig" ]; then
        echo '注意！你尚未安装豆瓣刮削器.';
        echo '==========================================================================';
        exit 1;
    else
        uninstall;
    fi;
elif [ "$vAction" == 'upgrade' ]; then
    install;
else
    echo '抱歉，安装豆瓣刮削器失败.';
    echo '==========================================================================';
    exit 1
fi;
