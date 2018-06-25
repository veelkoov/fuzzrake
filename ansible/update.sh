#!/usr/bin/env bash

#export AWS_ACCESS_KEY="$(grep aws_access_key_id ~/.aws/credentials | awk '{ print $3; }')"
#export AWS_SECRET_KEY="$(grep aws_secret_access_key ~/.aws/credentials | awk '{ print $3; }')"

ansible-playbook update.yaml "${@:1}"
