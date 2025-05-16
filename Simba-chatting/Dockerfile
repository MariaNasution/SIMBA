# syntax=docker/dockerfile:1

# --- Build Stage ---
ARG NODE_VERSION=22.14.0
FROM node:${NODE_VERSION}-slim AS builder
WORKDIR /app

# Install dependencies with cache and bind mounts for deterministic builds
COPY --link package.json package-lock.json ./
RUN --mount=type=cache,target=/root/.npm \
    --mount=type=bind,source=package.json,target=package.json \
    --mount=type=bind,source=package-lock.json,target=package-lock.json \
    npm ci

# Copy the rest of the application source
COPY --link public ./public
COPY --link src ./src
COPY --link scripts ./scripts
COPY --link README.md ./

# Build the React app
RUN --mount=type=cache,target=/root/.npm npm run build

# --- Production Stage ---
FROM node:${NODE_VERSION}-slim AS final
WORKDIR /app

# Create a non-root user
RUN addgroup --system appgroup && adduser --system --ingroup appgroup appuser

# Copy only the built app and minimal files
COPY --from=builder /app/build ./build
COPY --from=builder /app/package.json ./
COPY --from=builder /app/node_modules ./node_modules

ENV NODE_ENV=production
ENV NODE_OPTIONS="--max-old-space-size=4096"
USER appuser

EXPOSE 3000
CMD ["npx", "serve", "-s", "build", "-l", "3000"]
