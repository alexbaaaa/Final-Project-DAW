FROM wordpress:6.5-php8.3-apache

COPY docker/wordpress/entrypoint.sh /usr/local/bin/swimmingup-wordpress-entrypoint
RUN chmod +x /usr/local/bin/swimmingup-wordpress-entrypoint

EXPOSE 80

ENTRYPOINT ["swimmingup-wordpress-entrypoint"]
CMD ["apache2-foreground"]
