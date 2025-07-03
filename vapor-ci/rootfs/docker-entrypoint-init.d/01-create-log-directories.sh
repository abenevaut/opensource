#!/bin/sh

#
# Create log directories for xdebug and application logs
#

# Create storage/logs directory if it doesn't exist
mkdir -p /var/task/storage/logs

# Set proper permissions
chown -R nobody:nobody /var/task/storage/logs
chmod -R 755 /var/task/storage/logs

echo "Log directories created successfully"