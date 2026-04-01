FROM trafex/php-nginx:3.10.0

# Temporarily switch to root again so that we can install php84-xml
USER root
RUN apk add --no-cache php84-xml php84-simplexml php84-dom
# Switch back to non-root
USER nobody