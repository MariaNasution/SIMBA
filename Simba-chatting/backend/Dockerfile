# syntax=docker/dockerfile:1

# ----------- Build Stage -----------
ARG GO_VERSION=1.24.2
FROM golang:${GO_VERSION}-alpine AS builder

# Install git for go mod (if needed) and ca-certificates
RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache git ca-certificates

WORKDIR /app

# Copy go.mod and go.sum first for dependency caching
COPY --link go.mod go.sum ./

# Download dependencies (use cache mounts for speed)
RUN --mount=type=cache,target=/go/pkg/mod \
    --mount=type=cache,target=/root/.cache/go-build \
    go mod download

# Copy the rest of the source code
COPY --link . ./

# Build the Go binary (static build)
RUN --mount=type=cache,target=/go/pkg/mod \
    --mount=type=cache,target=/root/.cache/go-build \
    CGO_ENABLED=0 GOOS=linux go build -ldflags="-s -w" -o simba-chat-backend

# ----------- Final Stage -----------
FROM alpine:latest AS final

# Add non-root user
RUN addgroup -S simba && adduser -S simba -G simba

WORKDIR /app

# Install ca-certificates for HTTPS
RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache ca-certificates

# Copy the built binary from builder
COPY --from=builder /app/simba-chat-backend ./simba-chat-backend

# Copy the service-account.json (credentials file is NOT a secret, but should be mounted at runtime; this is for local dev only)
COPY --link service-account.json ./service-account.json

# Set permissions
RUN chown simba:simba ./simba-chat-backend ./service-account.json

USER simba

# Expose the port the app runs on
EXPOSE 8080

# Set environment variable for credentials (can be overridden at runtime)
ENV GOOGLE_APPLICATION_CREDENTIALS=/app/service-account.json

ENTRYPOINT ["./simba-chat-backend"]
