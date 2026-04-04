FROM php:8.2-apache-bookworm

# Mise à jour et installation des dépendances système nécessaires
RUN apt-get update && apt-get install -y --no-install-recommends \
    libaio1 \
    wget \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Téléchargement et extraction d'Oracle Instant Client
RUN mkdir -p /opt/oracle \
    && wget https://download.oracle.com/otn_software/linux/instantclient/2112000/instantclient-basic-linux.x64-21.12.0.0.0dbru.zip \
    && wget https://download.oracle.com/otn_software/linux/instantclient/2112000/instantclient-sdk-linux.x64-21.12.0.0.0dbru.zip \
    && unzip instantclient-basic-linux.x64-21.12.0.0.0dbru.zip -d /opt/oracle \
    && unzip instantclient-sdk-linux.x64-21.12.0.0.0dbru.zip -d /opt/oracle \
    && rm instantclient-*.zip

# Configuration des variables d'environnement pour Oracle
ENV LD_LIBRARY_PATH=/opt/oracle/instantclient_21_12
RUN echo /opt/oracle/instantclient_21_12 > /etc/ld.so.conf.d/oracle-instantclient.conf \
    && ldconfig

# Installation et activation de l'extension OCI8
RUN echo 'instantclient,/opt/oracle/instantclient_21_12' | pecl install oci8 \
    && docker-php-ext-enable oci8

# Ajustement pour que l'ID du serveur interne fonctionne bien sans s'arrêter
# (Apache est lancé par défaut avec l'image php:8.2-apache)
