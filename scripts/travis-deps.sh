#!/bin/sh

MEMCACHE_VERSION=2.2.6
APC_VERSION=3.1.9

CONFIGURE_ARGS="--silent --quiet"

install_ext_memcache() {
    wget "http://pecl.php.net/get/memcache-${MEMCACHE_VERSION}.tgz"
    tar -xzf "memcache-${MEMCACHE_VERSION}.tgz"
    sh -c "cd memcache-${MEMCACHE_VERSION} && phpize && ./configure --enable-memcache ${CONFIGURE_ARGS} && make && sudo make install"
    echo "extension=memcache.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
}

install_ext_apc() {
    wget "http://pecl.php.net/get/APC-${APC_VERSION}.tgz"
    tar -xzf "APC-${APC_VERSION}.tgz"
    sh -c "cd APC-${APC_VERSION} && phpize && ./configure --enable-apc ${CONFIGURE_ARGS} && make && sudo make install"
    echo "extension=apc.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
}

install_ext_memcache
install_ext_apc
