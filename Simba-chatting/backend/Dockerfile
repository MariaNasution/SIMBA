# syntax=docker/dockerfile:1

# ----------- Build Stage -----------
ARG GO_VERSION=1.24.1
FROM golang:${GO_VERSION}-alpine AS builder

# Install git for go mod (if needed)
RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache git

WORKDIR /app

# Copy go.mod and go.sum first for dependency caching
COPY --link go.mod go.sum ./

# Download dependencies (with cache mounts)
RUN --mount=type=cache,target=/go/pkg/mod \
    --mount=type=cache,target=/root/.cache/go-build \
    go mod download

# Copy the rest of the source code
COPY --link . .

# Build the Go binary (static build)
RUN --mount=type=cache,target=/go/pkg/mod \
    --mount=type=cache,target=/root/.cache/go-build \
    CGO_ENABLED=0 GOOS=linux go build -ldflags="-s -w" -o simba-chat-backend

# ----------- Final Stage -----------
FROM alpine:latest AS final

# Install CA certificates
RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache ca-certificates

# Create non-root user
RUN addgroup -S simba && adduser -S simba -G simba

WORKDIR /app

# Copy the built binary from builder
COPY --from=builder /app/simba-chat-backend ./simba-chat-backend

# Copy the service account json (needed at runtime, but NOT secrets; should be mounted in prod)
COPY --link service-account.json ./service-account.json

# Set permissions
RUN chown -R simba:simba /app
USER simba

# Expose the port
EXPOSE 8080

# Set environment variable for credentials (should be overridden in prod)
ENV GOOGLE_APPLICATION_CREDENTIALS=/app/service-account.json

# Run the application
ENTRYPOINT ["./simba-chat-backend"]
