FROM node:22-alpine

WORKDIR /app

EXPOSE 5173

CMD ["sh", "-c", "if [ -f package.json ]; then npm install && npm run dev -- --host 0.0.0.0; else echo 'Frontend React pendiente de crear en ./frontend'; tail -f /dev/null; fi"]
