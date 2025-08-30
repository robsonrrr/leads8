
#!/bin/bash
# arquivo gerado automaticamente por create-koseven.sh

docker service rm leads6

sleep 3

docker service create \
    --replicas 1 \
    --name leads6  \
    --network traefik-net   \
    --label  traefik.enable=true   \
    --label  'traefik.http.routers.leads6.rule=Host(`dev.office.internut.com.br`) && PathPrefix(`/leads6`)'  \
    --label  traefik.http.routers.leads6.entrypoints=websecure  \
    --label  traefik.http.routers.leads6.tls=true   \
    --label  traefik.http.routers.leads6.tls.certresolver=myresolver \
    --label  traefik.http.services.leads6.loadbalancer.server.port=80   \
    --label  traefik.http.routers.leads6.middlewares=leads6-stripprefix \
    --label  traefik.http.middlewares.leads6-stripprefix.stripprefix.prefixes=/leads6 \
    --mount type=bind,source=/home/ubuntu/environment/Office/Apps/inProduction/leads/leads6,destination=/var/www/html \
    --mount type=bind,source=/home/ubuntu/environment/Office/Configs/dev/config.env,destination=/data/config.env \
koseven-php8:latest
