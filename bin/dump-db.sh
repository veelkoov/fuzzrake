#!/usr/bin/env bash

pushd "$(dirname "$0")/../ansible"

ansible-playbook dump-db.yaml "${@:1}"
