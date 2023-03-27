# soffice-api
## Install
- docker build -t soffice-api .
- docker run --tty --detach --publish 30001:80 \
  --mount type=bind,source="$(pwd)",target=/srv/web,readonly \
  --restart always --name soffice-api soffice-api
