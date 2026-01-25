FROM trafex/php-nginx:latest

# Temporarily switch to root again so that we can install php84-xml
USER root
RUN apk add php84-xml
# Switch back to non-root
USER nobody