FROM nginx:alpine

RUN apk add --update bash curl

ADD start.sh /start.sh
RUN chmod 755 /start.sh
CMD ["/start.sh"]

ADD wait-for.sh /wait-for.sh
RUN chmod 755 /wait-for.sh
