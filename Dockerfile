FROM alpine:3.3

RUN apk add --update \
        php-zlib \
        php-mysqli && \

RUN apk add --update \
        php-zlib \
        php-mysqli && \
    rm -rf /var/cache/apk/* && \
    curl -0L http://wordpress.org/wordpress-4.2.2.tar.gz | tar zxv && \
    cd /wordpress/wp-content/plugins && \
    wget http://downloads.wordpress.org/plugin/woocommerce.zip;unzip woocommerce.zip;rm woocommerce.zip && \
    wget http://downloads.wordpress.org/plugin/wp-google-analytics-scripts.zip;unzip wp-google-analytics-scripts.zip;rm wp-google-analytics-scripts.zip && \
    cd /wordpress/wp-content/themes && \
    wget https://downloads.wordpress.org/theme/omega.1.2.3.zip; unzip omega.1.2.3.zip; rm omega.1.2.3.zip && \
    wget https://downloads.wordpress.org/theme/shopping.0.4.0.zip; unzip shopping.0.4.0.zip; rm shopping.0.4.0.zip

ENTRYPOINT ["/bin/sh", "/app/build/entrypoints/teleport.sh"]

CMD ["php-fpm", "-F", "-d error_reporting=E_ALL", "-d log_errors=ON", "-d error_log=/dev/stdout","-d display_errors=YES"]
