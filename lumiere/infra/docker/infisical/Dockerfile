FROM node:18-alpine

# Set environment variables
ENV NODE_ENV=production
ENV PORT=3000

# Install system dependencies
RUN apk add --no-cache \
    curl \
    wget \
    git \
    bash \
    openssl \
    ca-certificates \
    && rm -rf /var/cache/apk/*

# Install Infisical CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/infisical/infisical-cli/setup.alpine.sh' | bash \
    && apk add infisical

# Create working directory
WORKDIR /app

# Create user for security
RUN addgroup -g 1001 infisical && \
    adduser -u 1001 -G infisical -s /bin/bash -D infisical

# Create necessary directories
RUN mkdir -p /app/config \
             /app/data \
             /app/logs \
             /home/infisical/.infisical \
    && chown -R infisical:infisical /app /home/infisical

# Copy configuration files
COPY infisical.json /app/config/
COPY .env.example /app/

USER infisical

# Set up Infisical configuration directory
RUN chmod 700 /home/infisical/.infisical

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:${PORT}/health || exit 1

# Expose port
EXPOSE 3000

# Default command
CMD ["tail", "-f", "/dev/null"]
