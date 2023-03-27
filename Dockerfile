FROM ubuntu:latest
ENV TZ=Asia/Taipei
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt-get update -y
RUN apt-get upgrade -y
RUN apt-get install unoconv php-cli php-mbstring -y
VOLUME /srv/web
CMD ["php", "-S", "0:80", "/srv/web/web.php"]
