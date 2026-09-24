FROM node:20-alpine

WORKDIR /app

RUN apk add --no-cache tzdata git

COPY package*.json ./

RUN npm install --omit=dev

COPY . .

EXPOSE 3000

ENV PORT=3000
ENV NODE_ENV=production

CMD ["node", "server.js"]
