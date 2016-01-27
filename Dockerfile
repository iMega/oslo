FROM alpine:3.3

RUN apk add --update php-json && \
    rm -rf /var/cache/apk/*

COPY . /app

VOLUME ["/data"]

CMD ["/app/oslo", "parse"]
