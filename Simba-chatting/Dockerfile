# syntax=docker/dockerfile:1

# ----------- Build Stage -----------
ARG NODE_VERSION=22.13.1
FROM node:${NODE_VERSION}-slim AS builder
WORKDIR /app

# Install dependencies using npm ci for deterministic builds
COPY --link package.json package-lock.json ./
RUN --mount=type=cache,target=/root/.npm \
    npm ci

# Copy the rest of the application source code
COPY --link public ./public
COPY --link src ./src
COPY --link scripts ./scripts
COPY --link README.md ./

# Build the React app
RUN npm run build

# Remove dev dependencies for production
RUN rm -rf node_modules && npm ci --omit=dev

# ----------- Production Stage -----------
FROM node:${NODE_VERSION}-slim AS final
WORKDIR /app

# Create a non-root user and group
RUN addgroup --system appgroup && adduser --system --ingroup appgroup appuser

# Copy built app and production dependencies from builder
COPY --from=builder /app/build ./build
COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/package.json ./
COPY --from=builder /app/package-lock.json ./

ENV NODE_ENV=production
ENV NODE_OPTIONS="--max-old-space-size=4096"
USER appuser

EXPOSE 3000
CMD ["npx", "serve", "-s", "build", "-l", "3000"]
