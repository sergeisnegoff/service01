FROM node:14

RUN apt-get update; \
    apt-get install -y --no-install-recommends \
        libpng-dev \
    ;

RUN mkdir -p /app

WORKDIR /app

RUN mkdir /.npm; mkdir /.config; mkdir /.cache; mkdir /.local; chmod 777 /.npm; chmod 777 /.config; chmod 777 /.cache; chmod 777 /.local;

ENV HOST 0.0.0.0

CMD npm install --no-save && npm run start
