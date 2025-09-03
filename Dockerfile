FROM node:20
COPY . /app
WORKDIR /app
RUN npm install
RUN npm install -g typescript
RUN tsc
CMD ["node", "dist/index.js"]