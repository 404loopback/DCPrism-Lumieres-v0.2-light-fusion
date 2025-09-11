FROM alpine:3.19

# Install dependencies
RUN apk add --no-cache \
    ca-certificates \
    curl \
    wget \
    git \
    bash \
    openssh-client \
    unzip \
    jq \
    && rm -rf /var/cache/apk/*

# Install OpenTofu
ENV OPENTOFU_VERSION=1.6.2
RUN wget -q https://github.com/opentofu/opentofu/releases/download/v${OPENTOFU_VERSION}/tofu_${OPENTOFU_VERSION}_linux_amd64.zip \
    && unzip tofu_${OPENTOFU_VERSION}_linux_amd64.zip \
    && mv tofu /usr/local/bin/ \
    && chmod +x /usr/local/bin/tofu \
    && rm tofu_${OPENTOFU_VERSION}_linux_amd64.zip

# Create working directory
WORKDIR /workspace

# Create user for security
RUN addgroup -g 1001 opentofu && \
    adduser -u 1001 -G opentofu -s /bin/bash -D opentofu

# Create directories
RUN mkdir -p /workspace/terraform \
    /workspace/modules \
    /workspace/configs \
    /home/opentofu/.terraform.d \
    && chown -R opentofu:opentofu /workspace /home/opentofu

USER opentofu

# Set environment variables
ENV TF_DATA_DIR=/workspace/.terraform
ENV TF_PLUGIN_CACHE_DIR=/home/opentofu/.terraform.d/plugin-cache

# Create plugin cache directory
RUN mkdir -p $TF_PLUGIN_CACHE_DIR

# Default command
CMD ["tail", "-f", "/dev/null"]
