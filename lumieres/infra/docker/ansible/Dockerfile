FROM python:3.11-slim

# Set environment variables
ENV PYTHONUNBUFFERED=1
ENV ANSIBLE_HOST_KEY_CHECKING=False
ENV ANSIBLE_STDOUT_CALLBACK=yaml
ENV ANSIBLE_CALLBACKS_ENABLED=profile_tasks

# Install system dependencies
RUN apt-get update && apt-get install -y \
    openssh-client \
    git \
    curl \
    wget \
    rsync \
    sshpass \
    vim \
    nano \
    && rm -rf /var/lib/apt/lists/*

# Install Ansible and related packages
RUN pip install --no-cache-dir \
    ansible \
    ansible-core \
    ansible-runner \
    jinja2 \
    paramiko \
    requests \
    pyyaml \
    netaddr \
    dnspython \
    kubernetes \
    openshift \
    boto3 \
    botocore

# Install Ansible collections
RUN ansible-galaxy collection install \
    community.general \
    community.crypto \
    ansible.posix \
    kubernetes.core \
    community.kubernetes \
    amazon.aws \
    community.aws

# Create working directory
WORKDIR /ansible

# Create user for security
RUN useradd -m -u 1001 ansible \
    && mkdir -p /ansible/playbooks \
                /ansible/inventory \
                /ansible/roles \
                /ansible/group_vars \
                /ansible/host_vars \
                /ansible/files \
                /ansible/templates \
                /home/ansible/.ssh \
    && chown -R ansible:ansible /ansible /home/ansible

# Copy configuration files
COPY ansible.cfg /ansible/
COPY inventory/hosts /ansible/inventory/

USER ansible

# Set SSH configuration
RUN chmod 700 /home/ansible/.ssh

# Default command
CMD ["tail", "-f", "/dev/null"]
