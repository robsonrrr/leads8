
#!/bin/bash
# arquivo gerado automaticamente por create-koseven.sh

docker service rm leads8

sleep 3

docker service create \
    --replicas 1 \
    --name leads8  \
    --network traefik-net   \
    --label  traefik.enable=true   \
    --label  'traefik.http.routers.leads8.rule=Host(`dev.office.internut.com.br`) && PathPrefix(`/leads8/`)'  \
    --label  traefik.http.routers.leads8.entrypoints=websecure  \
    --label  traefik.http.routers.leads8.tls=true   \
    --label  traefik.http.routers.leads8.tls.certresolver=myresolver \
    --label  traefik.http.services.leads8.loadbalancer.server.port=80   \
    --label  traefik.http.routers.leads8.middlewares=leads8-stripprefix \
    --label  traefik.http.middlewares.leads8-stripprefix.stripprefix.prefixes=/leads8 \
    --mount type=bind,source=/home/ubuntu/environment/Office/Apps/inProduction/leads/leads8,destination=/var/www/html \
    --mount type=bind,source=/home/ubuntu/environment/Office/Configs/dev/config.env,destination=/data/config.env \
leads8:latest
