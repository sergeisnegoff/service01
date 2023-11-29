FROM node:14

RUN npm install pm2 -g

RUN mkdir -p /app

WORKDIR /app

RUN mkdir /.npm; mkdir /.config; mkdir /.cache; mkdir /.local; mkdir /.pm2; chmod 777 /.npm; chmod 777 /.config; chmod 777 /.cache; chmod 777 /.local; chmod 777 /.pm2;

ENV HOST 0.0.0.0

EXPOSE 3000

CMD npm run start
