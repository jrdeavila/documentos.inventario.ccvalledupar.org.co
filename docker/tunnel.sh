#!/bin/bash
# Uso de autossh para que se reconecte automáticamente
# -N: No ejecutar comandos remotos (solo túnel)
# -L: [PuertoLocal]:[DestinoRemoto]:[PuertoRemoto]
# -i: Ruta a la llave privada

exec autossh -M 0 -N \
    -o "ServerAliveInterval 30" \
    -o "ServerAliveCountMax 3" \
    -o "ExitOnForwardFailure=yes" \
    -L 33066:127.0.0.1:3306 \
    -L 33067:172.16.5.164:3306 \
    -L 5433:127.0.0.1:5432 \
    root@34.239.200.53 -i /root/.ssh/id_rsa

