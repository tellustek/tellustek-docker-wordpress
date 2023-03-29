FROM wordpress:latest


# Copying Themes and Plugins into the wordpress image
# COPY ["./src/themes", "/usr/src/wordpress/wp-content/themes"]
# COPY ["./src/plugins","/usr/src/wordpress/wp-content/plugins"]
COPY ["./src/restore","/usr/src/restore"]

# Applying the execution right on the folders for apache
COPY ./src/config/uploads.ini $PHP_INI_DIR/conf.d/uploads.ini

RUN set -eux; \
  if [ -n "$(find /usr/src/restore -maxdepth 1 -type f \( -name '*.zip' -o -name '*.jpa' \))" ]; then \
    chown -R www-data:www-data /usr/src/restore && \
    cp -r /usr/src/restore/* /usr/src/wordpress/; \
  fi

COPY ./docker-entrypoint.sh /usr/local/bin/

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]