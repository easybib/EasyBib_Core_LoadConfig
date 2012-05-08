#!/bin/sh

MEMCACHE_VERSION=2.2.6
APC_VERSION=3.1.9

CONFIGURE_ARGS="--silent --quiet"

install_composer() {
    wget http://getcomposer.org/composer.phar && php composer.phar install
}

install_ext_memcache() {
    wget "http://pecl.php.net/get/memcache-${MEMCACHE_VERSION}.tgz"
    tar -xzf "memcache-${MEMCACHE_VERSION}.tgz"
    sh -c "cd memcache-${MEMCACHE_VERSION} && phpize && ./configure --enable-memcache ${CONFIGURE_ARGS} && make && sudo make install"
    echo "extension=memcache.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
}

install_ext_apc() {
    wget "http://pecl.php.net/get/APC-${APC_VERSION}.tgz"
    tar -xzf "APC-${APC_VERSION}.tgz"
    sh -c "cd APC-${APC_VERSION} && phpize && ./configure --enable-apc ${CONFIGURE_ARGS} && make && sudo make install"
    echo "extension=apc.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
}

install_composer
install_ext_memcache
install_ext_apc
